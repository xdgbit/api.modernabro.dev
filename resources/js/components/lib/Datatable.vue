<script setup>
import {ref, computed, watch, onBeforeUnmount} from 'vue'
import {AgGridVue} from 'ag-grid-vue3'
import {themeQuartz} from 'ag-grid-community'

const props = defineProps({
    apiUrl: {
        type: String,
        required: true,
    },

    columnDefs: {
        type: Array,
        required: true,
    },

    extraParams: {
        type: Object,
        default: () => ({}),
    },

    height: {
        type: [String, Number],
        default: 'auto',
    },
    rowHeight: {
        type: Number,
        default: 44,
    },
    pageSize: {
        type: Number,
        default: 5,
    },
})

/*
|--------------------------------------------------------------------------
| State
|--------------------------------------------------------------------------
*/

const currentPage = ref(1)
const pageSize = ref(props.pageSize)
const totalRows = ref(0)
const search = ref('')
let searchTimeout = null
watch(search, () =>
{
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() =>
    {
        doSearch()
    }, 500)
})
onBeforeUnmount(() =>
{
    clearTimeout(searchTimeout)
})

const loading = ref(false)
const gridApi = ref(null)

/*
|--------------------------------------------------------------------------
| Container
|--------------------------------------------------------------------------
*/

const containerStyle = computed(() => ({
    height:
        props.height === 'auto'
            ? 'auto'
            : typeof props.height === 'number'
                ? `${props.height}px`
                : props.height,
}))

/*
|--------------------------------------------------------------------------
| Grid Height
|--------------------------------------------------------------------------
*/

const gridHeight = computed(() =>
{
    const rowHeight = props.rowHeight
    const headerHeight = 46
    const border = 2
    const extra = 10
    if (totalRows.value === 0)
    {
        return '100px'
    }

    const rows = Math.min(
        totalRows.value,
        pageSize.value
    )

    return `${headerHeight + (rows * rowHeight) + border + extra}px`
})

/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

const totalPages = computed(() =>
{
    return Math.ceil(
        totalRows.value / pageSize.value
    )
})

const paginationFrom = computed(() =>
{
    if (totalRows.value === 0)
    {
        return 0
    }

    return (
        (currentPage.value - 1) *
        pageSize.value
    ) + 1
})

const paginationTo = computed(() =>
{
    return Math.min(
        currentPage.value * pageSize.value,
        totalRows.value
    )
})

/*
 * خروجی نمونه برای 155 رکورد (16 صفحه):
 * صفحه 1  →  1 2 3 ... 16
 * صفحه 8  →  1 ... 7 8 9 ... 16
 * صفحه 16 →  1 ... 14 15 16
 */
const pages = computed(() =>
{
    const total = totalPages.value
    const current = currentPage.value

    // اگه صفحه‌ها کم باشن همه رو نشون بده
    if (total <= 5)
    {
        return Array.from({length: total}, (_, i) => i + 1)
    }

    // نزدیک ابتدا: 1 2 3 ... 16
    if (current <= 2)
    {
        return [1, 2, 3, '...', total]
    }

    // نزدیک انتها: 1 ... 14 15 16
    if (current >= total - 1)
    {
        return [1, '...', total - 2, total - 1, total]
    }

    // وسط: 1 ... 7 8 9 ... 16
    const middle = [current - 1, current, current + 1]
    const result = [1]

    if (middle[0] > 2)
    {
        result.push('...')
    }

    middle.forEach((p) =>
    {
        if (p > 1 && p < total)
        {
            result.push(p)
        }
    })

    if (middle[2] < total - 1)
    {
        result.push('...')
    }

    result.push(total)

    return result
})

/*
|--------------------------------------------------------------------------
| Change Page Size
|--------------------------------------------------------------------------
*/

const changePageSize = () =>
{
    currentPage.value = 1

    if (!gridApi.value)
    {
        return
    }

    gridApi.value.paginationGoToFirstPage()

    gridApi.value.purgeInfiniteCache()
}

/*
|--------------------------------------------------------------------------
| Go To Page
|--------------------------------------------------------------------------
*/

const goToPage = (page) =>
{
    if (!gridApi.value)
    {
        return
    }

    if (
        page < 1 ||
        page > totalPages.value
    )
    {
        return
    }

    currentPage.value = page

    gridApi.value.paginationGoToPage(
        page - 1
    )
}

/*
|--------------------------------------------------------------------------
| Search
|--------------------------------------------------------------------------
*/

const doSearch = () =>
{
    if (!gridApi.value)
    {
        return
    }

    currentPage.value = 1

    gridApi.value.paginationGoToFirstPage()

    gridApi.value.purgeInfiniteCache()
}

/*
|--------------------------------------------------------------------------
| Theme
|--------------------------------------------------------------------------
*/

const theme = themeQuartz.withParams({
    fontFamily: 'IRANSans, sans-serif',
    fontSize: 13,
    backgroundColor: '#ffffff',
    foregroundColor: '#334155',
    headerBackgroundColor: '#f8fafc',
    headerTextColor: '#334155',
    headerFontWeight: 600,
    borderColor: '#e2e8f0',
    rowHoverColor: '#f8fafc',
    rowHeight: props.rowHeight,
    headerHeight: 46,
    paginationPanelHeight: 52,
})

/*
|--------------------------------------------------------------------------
| Persian
|--------------------------------------------------------------------------
*/

const localeText = {
    page: 'صفحه',
    more: 'بیشتر',
    to: 'تا',
    of: 'از',
    next: 'بعدی',
    last: 'آخرین',
    first: 'اولین',
    previous: 'قبلی',
    pageSize: 'تعداد در صفحه',
    loadingOoo: 'در حال بارگذاری...',
    noRowsToShow: 'رکوردی برای نمایش وجود ندارد',
    filterOoo: 'فیلتر...',
    equals: 'برابر است با',
    notEqual: 'برابر نیست با',
    contains: 'شامل',
    notContains: 'شامل نیست',
    startsWith: 'شروع می‌شود با',
    endsWith: 'تمام می‌شود با',
    blank: 'خالی',
    notBlank: 'خالی نیست',
    resetFilter: 'حذف فیلتر',
    clearFilter: 'پاک کردن فیلتر',
    applyFilter: 'اعمال فیلتر',
}

/*
|--------------------------------------------------------------------------
| Datasource
|--------------------------------------------------------------------------
*/


const createDatasource = () => ({
    getRows: async (params) =>
    {
        loading.value = true

        try
        {
            const length = pageSize.value
            const page = Math.floor(params.startRow / length) + 1

            const sort = params.sortModel?.[0]

            const response = await axios.get(props.apiUrl, {
                params: {
                    page,
                    length,
                    search: search.value,
                    column_name: sort?.colId ?? 'id',
                    dir: sort?.sort ?? 'desc',
                    ...props.extraParams,
                },
            })

            const result = response.data

            totalRows.value = Number(result.meta?.total) || 0
            currentPage.value = Number(result.meta?.current_page) || page

            params.successCallback(
                result.data || [],
                totalRows.value
            )
        } catch (error)
        {
            console.error('Datatable Error:', error)
            totalRows.value = 0
            params.failCallback()
        } finally
        {
            loading.value = false
        }
    },
})


/*
|--------------------------------------------------------------------------
| Grid Ready
|--------------------------------------------------------------------------
*/

const onGridReadyWithDatasource = (params) =>
{
    gridApi.value = params.api

    params.api.setGridOption(
        'datasource',
        createDatasource()
    )
}

/*
|--------------------------------------------------------------------------
| Refresh
|--------------------------------------------------------------------------
*/

const refreshGrid = () =>
{
    if (!gridApi.value)
    {
        return
    }

    gridApi.value.purgeInfiniteCache()
}

/*
|--------------------------------------------------------------------------
| Expose
|--------------------------------------------------------------------------
*/

defineExpose({
    refreshGrid,
})
</script>

<template>
    <div
        class="data-table"
        :style="containerStyle"
    >

        <!-- Toolbar -->

        <div class="grid-toolbar">

            <div class="page-size-area">

                <span>
                    نمایش
                </span>

                <select
                    v-model.number="pageSize"
                    class="page-size-select"
                    @change="changePageSize"
                >
                    <option :value="5">
                        5
                    </option>

                    <option :value="10">
                        10
                    </option>

                    <option :value="20">
                        20
                    </option>

                    <option :value="50">
                        50
                    </option>

                    <option :value="100">
                        100
                    </option>
                </select>

                <span>
                    رکورد در صفحه
                </span>

            </div>

            <!-- Search -->

            <div class="search-area">
                <div class="search-box">
                    <i class="fa fa-search"></i>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="جستجو..."
                    />

                    <button
                        v-if="search"
                        type="button"
                        class="clear-search"
                        @click="search = ''"
                    >
                        <i class="fa fa-times"></i>
                    </button>

                </div>

            </div>

        </div>

        <!-- Grid -->

        <div
            class="grid-wrapper"
            :style="{ height: gridHeight }"
        >

            <AgGridVue
                class="admin-grid"
                :theme="theme"
                :columnDefs="columnDefs"
                :localeText="localeText"
                :enableRtl="true"
                rowModelType="infinite"
                :cacheBlockSize="pageSize"
                :pagination="true"
                :paginationPageSize="pageSize"
                :suppressPaginationPanel="true"
                :row-height="props.rowHeight"
                @grid-ready="onGridReadyWithDatasource"
            />

            <!-- Loading -->

            <Transition name="loading-fade">

                <div
                    v-if="loading"
                    class="grid-loading"
                >

                    <div class="loading-box">

                        <div class="spinner"></div>

                        <div class="loading-title">
                            در حال بارگذاری
                        </div>

                        <div class="loading-text">
                            لطفاً منتظر بمانید...
                        </div>

                    </div>

                </div>

            </Transition>

        </div>

        <!-- Pagination -->

        <div class="pagination-footer">

            <div class="record-info">

                نمایش

                <strong>
                    {{ paginationFrom }}
                </strong>

                تا

                <strong>
                    {{ paginationTo }}
                </strong>

                از

                <strong>
                    {{ totalRows }}
                </strong>

                رکورد

            </div>

            <div class="pagination">

                <!-- قبلی -->
                <button
                    type="button"
                    class="page-button"
                    :disabled="currentPage <= 1"
                    @click="goToPage(currentPage - 1)"
                >
                    <i class="fa fa-chevron-right"></i>
                </button>

                <!-- شماره صفحه‌ها -->
                <template
                    v-for="(page, index) in pages"
                    :key="index"
                >

                    <span
                        v-if="page === '...'"
                        class="page-dots"
                    >
                        ...
                    </span>

                    <button
                        v-else
                        type="button"
                        class="page-button"
                        :class="{ active: page === currentPage }"
                        @click="goToPage(page)"
                    >
                        {{ page }}
                    </button>

                </template>

                <!-- بعدی -->
                <button
                    type="button"
                    class="page-button"
                    :disabled="currentPage >= totalPages"
                    @click="goToPage(currentPage + 1)"
                >
                    <i class="fa fa-chevron-left"></i>
                </button>

            </div>

        </div>

    </div>
</template>

<style scoped>
.page-dots {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    color: #94a3b8;
    user-select: none;
}
</style>