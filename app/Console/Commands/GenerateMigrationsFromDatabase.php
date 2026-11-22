<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class GenerateMigrationsFromDatabase extends Command
{
    protected $signature = 'db:generate-migrations
                            {--connection= : Database connection name}
                            {--path= : Output directory}
                            {--prefix= : Migration filename prefix}';

    protected $description = 'Generate Laravel migrations from an existing MySQL/MariaDB database';

    protected $db;

    protected string $database;

    protected string $outputPath;

    public function handle(): int
    {
        $connection = $this->option('connection')
            ?: config('database.default');

        $this->db = DB::connection($connection);
        $this->database = $this->db->getDatabaseName();

        $this->outputPath = $this->option('path')
            ?: database_path('migrations');

        File::ensureDirectoryExists($this->outputPath);

        $this->info("Database: {$this->database}");
        $this->info("Connection: {$connection}");
        $this->newLine();

        $tables = $this->getTables();

        if (empty($tables)) {
            $this->error('No tables found.');

            return self::FAILURE;
        }

        $schema = [];

        foreach ($tables as $table) {
            $tableName = $table->TABLE_NAME;

            $this->line("Reading: {$tableName}");

            $schema[$tableName] = [
                'table' => $table,
                'columns' => $this->getColumns($tableName),
                'indexes' => $this->getIndexes($tableName),
                'foreign_keys' => $this->getForeignKeys($tableName),
            ];
        }

        $this->newLine();
        $this->info('Generating migrations...');

        $this->generateMigrations($schema);

        $this->newLine();
        $this->info('Done.');
        $this->line('Generated: ' . count($schema) . ' migration files.');
        $this->line('Path: ' . $this->outputPath);

        return self::SUCCESS;
    }

    /*
    |--------------------------------------------------------------------------
    | Database Information
    |--------------------------------------------------------------------------
    */

    protected function getTables(): array
    {
        return $this->db->select(
            "
            SELECT
                TABLE_NAME,
                ENGINE,
                TABLE_COLLATION,
                TABLE_COMMENT
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = ?
              AND TABLE_TYPE = 'BASE TABLE'
            ORDER BY TABLE_NAME
            ",
            [$this->database]
        );
    }

    protected function getColumns(string $table): array
    {
        return $this->db->select(
            "
            SELECT
                COLUMN_NAME,
                ORDINAL_POSITION,
                COLUMN_DEFAULT,
                IS_NULLABLE,
                DATA_TYPE,
                COLUMN_TYPE,
                CHARACTER_MAXIMUM_LENGTH,
                CHARACTER_OCTET_LENGTH,
                NUMERIC_PRECISION,
                NUMERIC_SCALE,
                DATETIME_PRECISION,
                CHARACTER_SET_NAME,
                COLLATION_NAME,
                COLUMN_KEY,
                EXTRA,
                COLUMN_COMMENT,
                GENERATION_EXPRESSION
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
            ORDER BY ORDINAL_POSITION
            ",
            [$this->database, $table]
        );
    }

    protected function getIndexes(string $table): array
    {
        $rows = $this->db->select(
            "
            SELECT
                INDEX_NAME,
                NON_UNIQUE,
                SEQ_IN_INDEX,
                COLUMN_NAME,
                SUB_PART,
                INDEX_TYPE
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
            ORDER BY INDEX_NAME, SEQ_IN_INDEX
            ",
            [$this->database, $table]
        );

        $indexes = [];

        foreach ($rows as $row) {
            if ($row->INDEX_NAME === 'PRIMARY') {
                continue;
            }

            if (!isset($indexes[$row->INDEX_NAME])) {
                $indexes[$row->INDEX_NAME] = [
                    'unique' => !(bool) $row->NON_UNIQUE,
                    'type' => strtoupper($row->INDEX_TYPE),
                    'columns' => [],
                    'prefixes' => [],
                ];
            }

            $indexes[$row->INDEX_NAME]['columns'][] = $row->COLUMN_NAME;
            $indexes[$row->INDEX_NAME]['prefixes'][] = $row->SUB_PART;
        }

        return $indexes;
    }

    protected function getForeignKeys(string $table): array
    {
        return $this->db->select(
            "
            SELECT
                kcu.CONSTRAINT_NAME,
                kcu.ORDINAL_POSITION,
                kcu.COLUMN_NAME,
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME,
                rc.UPDATE_RULE,
                rc.DELETE_RULE
            FROM information_schema.KEY_COLUMN_USAGE kcu
            INNER JOIN information_schema.REFERENTIAL_CONSTRAINTS rc
                ON rc.CONSTRAINT_SCHEMA = kcu.CONSTRAINT_SCHEMA
                AND rc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
            WHERE kcu.TABLE_SCHEMA = ?
              AND kcu.TABLE_NAME = ?
              AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
            ORDER BY
                kcu.CONSTRAINT_NAME,
                kcu.ORDINAL_POSITION
            ",
            [$this->database, $table]
        );
    }

    protected function getPrimaryKey(string $table): array
    {
        $rows = $this->db->select(
            "
            SELECT
                COLUMN_NAME,
                SEQ_IN_INDEX
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = ?
              AND TABLE_NAME = ?
              AND INDEX_NAME = 'PRIMARY'
            ORDER BY SEQ_IN_INDEX
            ",
            [$this->database, $table]
        );

        return array_map(
            fn ($row) => $row->COLUMN_NAME,
            $rows
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generate
    |--------------------------------------------------------------------------
    */

    protected function generateMigrations(array $schema): void
    {
        $timestamp = now()->startOfSecond();

        $index = 0;

        foreach ($schema as $tableName => $data) {
            $timestamp = $timestamp->copy()->addSecond();

            $filename = $timestamp->format('Y_m_d_His')
                . '_create_'
                . Str::snake($tableName)
                . '_table.php';

            $path = $this->outputPath . DIRECTORY_SEPARATOR . $filename;

            $content = $this->buildMigration(
                $tableName,
                $data
            );

            File::put($path, $content);

            $index++;

            $this->line("  ✓ {$filename}");
        }
    }

    protected function buildMigration(string $tableName, array $data): string
    {
        $table = $data['table'];
        $columns = $data['columns'];
        $indexes = $data['indexes'];
        $foreignKeys = $data['foreign_keys'];

        $primaryKey = $this->getPrimaryKey($tableName);

        $output = "<?php\n\n";

        $output .= "use Illuminate\\Database\\Migrations\\Migration;\n";
        $output .= "use Illuminate\\Database\\Schema\\Blueprint;\n";
        $output .= "use Illuminate\\Support\\Facades\\Schema;\n";
        $output .= "use Illuminate\\Support\\Facades\\DB;\n\n";

        $output .= "return new class extends Migration\n";
        $output .= "{\n";

        /*
        |--------------------------------------------------------------------------
        | UP
        |--------------------------------------------------------------------------
        */

        $output .= "    public function up(): void\n";
        $output .= "    {\n";

        $output .= "        Schema::create("
            . var_export($tableName, true)
            . ", function (Blueprint \$table) {\n";

        /*
         * Table options
         */

        if (!empty($table->ENGINE)) {
            $output .= "            \$table->engine = "
                . var_export($table->ENGINE, true)
                . ";\n";
        }

        if (!empty($table->TABLE_COLLATION)) {
            $charset = Str::before(
                $table->TABLE_COLLATION,
                '_'
            );

            $output .= "            \$table->charset = "
                . var_export($charset, true)
                . ";\n";

            $output .= "            \$table->collation = "
                . var_export($table->TABLE_COLLATION, true)
                . ";\n";
        }

        $output .= "\n";

        /*
         * Columns
         */

        foreach ($columns as $column) {
            $output .= "            "
                . $this->buildColumn($column)
                . "\n";
        }

        /*
         * Primary key
         */

        if (!empty($primaryKey)) {
            $output .= "\n";

            $output .= "            \$table->primary("
                . $this->phpArray($primaryKey)
                . ");\n";
        }

        /*
         * Indexes
         */

        foreach ($indexes as $indexName => $index) {
            $output .= "\n";

            $columnsCode = $this->phpArray(
                $index['columns']
            );

            /*
             * FULLTEXT
             */

            if ($index['type'] === 'FULLTEXT') {
                $output .= "            \$table->fullText("
                    . $columnsCode
                    . ", "
                    . var_export($indexName, true)
                    . ");\n";

                continue;
            }

            /*
             * SPATIAL
             */

            if ($index['type'] === 'SPATIAL') {
                $output .= "            \$table->spatialIndex("
                    . $columnsCode
                    . ", "
                    . var_export($indexName, true)
                    . ");\n";

                continue;
            }

            /*
             * UNIQUE
             */

            if ($index['unique']) {
                $output .= "            \$table->unique("
                    . $columnsCode
                    . ", "
                    . var_export($indexName, true)
                    . ");\n";

                continue;
            }

            /*
             * Normal index
             */

            $output .= "            \$table->index("
                . $columnsCode
                . ", "
                . var_export($indexName, true)
                . ");\n";
        }

        $output .= "        });\n";

        /*
        |--------------------------------------------------------------------------
        | Foreign Keys
        |--------------------------------------------------------------------------
        */

        foreach ($this->groupForeignKeys($foreignKeys) as $foreignKey) {
            $output .= "\n";

            $output .= "        Schema::table("
                . var_export($tableName, true)
                . ", function (Blueprint \$table) {\n";

            $columnsCode = $this->phpArray(
                $foreignKey['columns']
            );

            $referencesCode = $this->phpArray(
                $foreignKey['referenced_columns']
            );

            $output .= "            \$table->foreign("
                . $columnsCode
                . ", "
                . var_export($foreignKey['name'], true)
                . ")\n";

            $output .= "                ->references("
                . $referencesCode
                . ")\n";

            $output .= "                ->on("
                . var_export(
                    $foreignKey['referenced_table'],
                    true
                )
                . ")";

            if ($foreignKey['delete_rule']) {
                $output .= "\n                ->onDelete("
                    . var_export(
                        strtolower($foreignKey['delete_rule']),
                        true
                    )
                    . ")";
            }

            if ($foreignKey['update_rule']) {
                $output .= "\n                ->onUpdate("
                    . var_export(
                        strtolower($foreignKey['update_rule']),
                        true
                    )
                    . ")";
            }

            $output .= ";\n";

            $output .= "        });\n";
        }

        $output .= "    }\n\n";

        /*
        |--------------------------------------------------------------------------
        | DOWN
        |--------------------------------------------------------------------------
        */

        $output .= "    public function down(): void\n";
        $output .= "    {\n";

        $output .= "        Schema::disableForeignKeyConstraints();\n";

        $output .= "        Schema::dropIfExists("
            . var_export($tableName, true)
            . ");\n";

        $output .= "        Schema::enableForeignKeyConstraints();\n";

        $output .= "    }\n";
        $output .= "};\n";

        return $output;
    }

    /*
    |--------------------------------------------------------------------------
    | Columns
    |--------------------------------------------------------------------------
    */

    protected function buildColumn(object $column): string
    {
        $name = var_export(
            $column->COLUMN_NAME,
            true
        );

        $type = strtolower(
            $column->DATA_TYPE
        );

        $columnType = strtolower(
            $column->COLUMN_TYPE
        );

        /*
         * Generated columns
         */

        if (!empty($column->GENERATION_EXPRESSION)) {
            $expression = var_export(
                $column->GENERATION_EXPRESSION,
                true
            );

            $method = str_contains(
                strtolower($column->EXTRA ?? ''),
                'stored'
            )
                ? 'storedAs'
                : 'virtualAs';

            $code = "\$table->"
                . $this->generatedColumnType($type)
                . "({$name})"
                . "->{$method}({$expression})";

            return $code . ";";
        }

        /*
         * ENUM
         */

        if ($type === 'enum') {
            preg_match_all(
                "/'((?:[^'\\\\]|\\\\.)*)'/",
                $column->COLUMN_TYPE,
                $matches
            );

            $values = array_map(
                fn ($value) => var_export(
                    stripslashes($value),
                    true
                ),
                $matches[1]
            );

            $code = "\$table->enum("
                . $name
                . ", ["
                . implode(', ', $values)
                . "])";
        }

        /*
         * SET
         */

        elseif ($type === 'set') {
            preg_match_all(
                "/'((?:[^'\\\\]|\\\\.)*)'/",
                $column->COLUMN_TYPE,
                $matches
            );

            $values = array_map(
                fn ($value) => var_export(
                    stripslashes($value),
                    true
                ),
                $matches[1]
            );

            $code = "\$table->set("
                . $name
                . ", ["
                . implode(', ', $values)
                . "])";
        }

        /*
         * TINYINT(1)
         */

        elseif (
            $type === 'tinyint'
            && preg_match('/tinyint\(1\)/i', $columnType)
        ) {
            $code = "\$table->boolean({$name})";
        }

        /*
         * Integer
         */

        elseif (
            in_array(
                $type,
                [
                    'tinyint',
                    'smallint',
                    'mediumint',
                    'int',
                    'integer',
                    'bigint',
                ],
                true
            )
        ) {
            $method = match ($type) {
                'tinyint' => 'tinyInteger',
                'smallint' => 'smallInteger',
                'mediumint' => 'mediumInteger',
                'bigint' => 'bigInteger',
                default => 'integer',
            };

            $code = "\$table->{$method}({$name})";

            if (str_contains($columnType, 'unsigned')) {
                $code .= "->unsigned()";
            }
        }

        /*
         * Decimal
         */

        elseif (
            in_array($type, ['decimal', 'numeric'], true)
        ) {
            $precision = $column->NUMERIC_PRECISION ?? 8;
            $scale = $column->NUMERIC_SCALE ?? 2;

            $code = "\$table->decimal("
                . $name
                . ", "
                . $precision
                . ", "
                . $scale
                . ")";
        }

        /*
         * Float
         */

        elseif ($type === 'float') {
            $code = "\$table->float({$name})";
        }

        /*
         * Double
         */

        elseif ($type === 'double') {
            $code = "\$table->double({$name})";
        }

        /*
         * Real
         */

        elseif ($type === 'real') {
            $code = "\$table->double({$name})";
        }

        /*
         * CHAR
         */

        elseif ($type === 'char') {
            $length = $column->CHARACTER_MAXIMUM_LENGTH ?: 255;

            $code = "\$table->char("
                . $name
                . ", "
                . $length
                . ")";
        }

        /*
         * VARCHAR
         */

        elseif ($type === 'varchar') {
            $length = $column->CHARACTER_MAXIMUM_LENGTH ?: 255;

            $code = "\$table->string("
                . $name
                . ", "
                . $length
                . ")";
        }

        /*
         * TEXT
         */

        elseif ($type === 'tinytext') {
            $code = "\$table->tinyText({$name})";
        }

        elseif ($type === 'text') {
            $code = "\$table->text({$name})";
        }

        elseif ($type === 'mediumtext') {
            $code = "\$table->mediumText({$name})";
        }

        elseif ($type === 'longtext') {
            $code = "\$table->longText({$name})";
        }

        /*
         * Binary
         */

        elseif (
            in_array(
                $type,
                [
                    'binary',
                    'varbinary',
                    'tinyblob',
                    'blob',
                    'mediumblob',
                    'longblob',
                ],
                true
            )
        ) {
            $code = "\$table->binary({$name})";
        }

        /*
         * JSON
         */

        elseif ($type === 'json') {
            $code = "\$table->json({$name})";
        }

        /*
         * Date
         */

        elseif ($type === 'date') {
            $code = "\$table->date({$name})";
        }

        /*
         * DateTime
         */

        elseif ($type === 'datetime') {
            $code = "\$table->dateTime({$name})";

            if (!empty($column->DATETIME_PRECISION)) {
                $code = "\$table->dateTime("
                    . $name
                    . ", "
                    . $column->DATETIME_PRECISION
                    . ")";
            }
        }

        /*
         * Timestamp
         */

        elseif ($type === 'timestamp') {
            $code = "\$table->timestamp({$name})";

            if (!empty($column->DATETIME_PRECISION)) {
                $code = "\$table->timestamp("
                    . $name
                    . ", "
                    . $column->DATETIME_PRECISION
                    . ")";
            }
        }

        /*
         * Time
         */

        elseif ($type === 'time') {
            $code = "\$table->time({$name})";
        }

        /*
         * Year
         */

        elseif ($type === 'year') {
            $code = "\$table->year({$name})";
        }

        /*
         * UUID
         */

        elseif ($type === 'uuid') {
            $code = "\$table->uuid({$name})";
        }

        /*
         * Geometry
         */

        elseif ($type === 'geometry') {
            $code = "\$table->geometry({$name})";
        }

        elseif ($type === 'point') {
            $code = "\$table->point({$name})";
        }

        /*
         * Fallback
         */

        else {
            $code = "\$table->string({$name})";
        }

        /*
        |--------------------------------------------------------------------------
        | Modifiers
        |--------------------------------------------------------------------------
        */

        /*
         * Unsigned
         */

        if (
            !str_contains(
                $code,
                'unsigned()'
            )
            && str_contains(
                $columnType,
                'unsigned'
            )
            && in_array(
                $type,
                [
                    'tinyint',
                    'smallint',
                    'mediumint',
                    'int',
                    'integer',
                    'bigint',
                    'decimal',
                    'float',
                    'double',
                ],
                true
            )
        ) {
            $code .= "->unsigned()";
        }

        /*
         * Nullable
         */

        if ($column->IS_NULLABLE === 'YES') {
            $code .= "->nullable()";
        }

        /*
         * Default
         */

        if ($column->COLUMN_DEFAULT !== null) {
            $default = $column->COLUMN_DEFAULT;

            if (
                preg_match(
                    '/^CURRENT_TIMESTAMP(?:\(\d+\))?$/i',
                    trim((string) $default)
                )
            ) {
                $code .= "->default(DB::raw("
                    . var_export($default, true)
                    . "))";
            } elseif (
                is_numeric($default)
                && !in_array(
                    $type,
                    ['char', 'varchar', 'text', 'enum', 'set'],
                    true
                )
            ) {
                $code .= "->default("
                    . $default
                    . ")";
            } elseif (
                strtolower((string) $default) === 'null'
            ) {
                $code .= "->default(null)";
            } else {
                $code .= "->default("
                    . var_export($default, true)
                    . ")";
            }
        }

        /*
         * Auto increment
         */

        if (
            str_contains(
                strtolower($column->EXTRA ?? ''),
                'auto_increment'
            )
        ) {
            $code .= "->autoIncrement()";
        }

        /*
         * Charset
         */

        if (!empty($column->CHARACTER_SET_NAME)) {
            $code .= "->charset("
                . var_export(
                    $column->CHARACTER_SET_NAME,
                    true
                )
                . ")";
        }

        /*
         * Collation
         */

        if (!empty($column->COLLATION_NAME)) {
            $code .= "->collation("
                . var_export(
                    $column->COLLATION_NAME,
                    true
                )
                . ")";
        }

        /*
         * Comment
         */

        if (!empty($column->COLUMN_COMMENT)) {
            $code .= "->comment("
                . var_export(
                    $column->COLUMN_COMMENT,
                    true
                )
                . ")";
        }

        /*
         * ON UPDATE CURRENT_TIMESTAMP
         */

        if (
            str_contains(
                strtolower($column->EXTRA ?? ''),
                'on update current_timestamp'
            )
        ) {
            $code .= "->useCurrentOnUpdate()";
        }

        return $code . ";";
    }

    protected function generatedColumnType(string $type): string
    {
        return match ($type) {
            'tinyint' => 'tinyInteger',
            'smallint' => 'smallInteger',
            'mediumint' => 'mediumInteger',
            'bigint' => 'bigInteger',
            'int', 'integer' => 'integer',
            'decimal', 'numeric' => 'decimal',
            'float' => 'float',
            'double' => 'double',
            'date' => 'date',
            'datetime' => 'dateTime',
            'timestamp' => 'timestamp',
            'json' => 'json',
            default => 'string',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Foreign Keys
    |--------------------------------------------------------------------------
    */

    protected function groupForeignKeys(array $rows): array
    {
        $groups = [];

        foreach ($rows as $row) {
            $name = $row->CONSTRAINT_NAME;

            if (!isset($groups[$name])) {
                $groups[$name] = [
                    'name' => $name,
                    'columns' => [],
                    'referenced_table' => $row->REFERENCED_TABLE_NAME,
                    'referenced_columns' => [],
                    'delete_rule' => $row->DELETE_RULE,
                    'update_rule' => $row->UPDATE_RULE,
                ];
            }

            $groups[$name]['columns'][] =
                $row->COLUMN_NAME;

            $groups[$name]['referenced_columns'][] =
                $row->REFERENCED_COLUMN_NAME;
        }

        return array_values($groups);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function phpArray(array $items): string
    {
        $values = array_map(
            fn ($item) => var_export($item, true),
            $items
        );

        return '[' . implode(', ', $values) . ']';
    }
}