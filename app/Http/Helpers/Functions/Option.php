<?php

if (!function_exists('payment_status_options'))
{
    function payment_status_options()
    {
        $payment_status = config('constants.payment_status');
        $payment_status_options = [];
        foreach ($payment_status as $key => $text)
        {
            $payment_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $payment_status_options;
    }
}
if (!function_exists('answer_status_options'))
{
    function answer_status_options()
    {
        $answer_status = config('constants.answer_status');
        $answer_status_options = [];
        foreach ($answer_status as $key => $text)
        {
            $answer_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $answer_status_options;
    }
}
if (!function_exists('stu_filter_columns'))
{
    function stu_filter_columns()
    {
        $base_currency_options = config('constants.stu_filter_columns');
        $base_currency_option = [];
        foreach ($base_currency_options as $key => $text)
        {
            $base_currency_option[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $base_currency_option;
    }
}
if (!function_exists('bfu_filter_columns'))
{
    function bfu_filter_columns()
    {
        $base_currency_options = config('constants.bfu_filter_columns');
        $base_currency_option = [];
        foreach ($base_currency_options as $key => $text)
        {
            $base_currency_option[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $base_currency_option;
    }
}
if (!function_exists('base_currency_options'))
{
    function base_currency_options()
    {
        $base_currency_options = config('constants.base_currency_options');
        $base_currency_option = [];
        foreach ($base_currency_options as $key => $text)
        {
            $base_currency_option[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $base_currency_option;
    }
}
if (!function_exists('integrated_options_for_p2p_dc'))
{
    function integrated_options_for_p2p_dc()
    {
        $answer_status = config('constants.integrated_options_for_p2p_dc');
        $answer_status_options = [];
        foreach ($answer_status as $key => $text)
        {
            $answer_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $answer_status_options;
    }
}
if (!function_exists('options_type_mission'))
{
    function options_type_mission()
    {
        $type_mission = config('constants.options_type_mission');
        $type_mission_options = [];
        foreach ($type_mission as $key => $text)
        {
            $type_mission_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $type_mission_options;
    }
}
if (!function_exists('dev_title_options'))
{
    function dev_title_options()
    {
        $answer_status = config('constants.dev_title_options');
        $answer_status_options = [];
        foreach ($answer_status as $key => $text)
        {
            $answer_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $answer_status_options;
    }
}
if (!function_exists('route_pages_options'))
{
    function route_pages_options()
    {
        $answer_status = config('constants.route_pages_options');
        $answer_status_options = [];
        foreach ($answer_status as $key => $text)
        {
            $answer_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $answer_status_options;
    }
}
if (!function_exists('authentication_status_options'))
{
    function authentication_status_options()
    {
        $authentication_status = config('constants.authentication_status');
        $authentication_status_options = [];
        foreach ($authentication_status as $key => $text)
        {
            $authentication_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $authentication_status_options;
    }
}

if (!function_exists('basic_authentication_status_options'))
{
    function basic_authentication_status_options()
    {
        $basic_authentication_status = config('constants.basic_authentication_status');
        $basic_authentication_status_options = [];
        foreach ($basic_authentication_status as $key => $text)
        {
            $basic_authentication_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $basic_authentication_status_options;
    }
}

if (!function_exists('full_authentication_status_options'))
{
    function full_authentication_status_options()
    {
        $full_authentication_status = config('constants.full_authentication_status');
        $full_authentication_status_options = [];
        foreach ($full_authentication_status as $key => $text)
        {
            $full_authentication_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $full_authentication_status_options;
    }
}

if (!function_exists('email_status_options'))
{
    function email_status_options()
    {
        $email_status = config('constants.email_status');
        $email_status_options = [];
        foreach ($email_status as $key => $text)
        {
            $email_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $email_status_options;
    }
}

if (!function_exists('mobile_status_options'))
{
    function mobile_status_options()
    {
        $mobile_status = config('constants.mobile_status');
        $mobile_status_options = [];
        foreach ($mobile_status as $key => $text)
        {
            $mobile_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $mobile_status_options;
    }
}

if (!function_exists('phone_status_options'))
{
    function phone_status_options()
    {

        $phone_status = config('constants.phone_status');
        $phone_status_options = [];
        foreach ($phone_status as $key => $text)
        {
            $phone_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $phone_status_options;
    }
}

if (!function_exists('bank_status_options'))
{
    function bank_status_options()
    {
        $bank_status = config('constants.bank_status');
        $bank_status_options = [];
        foreach ($bank_status as $key => $text)
        {
            $bank_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $bank_status_options;
    }
}

if (!function_exists('authentication_document_status_options'))
{
    function authentication_document_status_options()
    {
        $authentication_document_status = config('constants.authentication_document_status');
        $authentication_document_status_options = [];
        foreach ($authentication_document_status as $key => $text)
        {
            $authentication_document_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $authentication_document_status_options;
    }
}


if (!function_exists('financial_withdrawal_status_option'))
{
    function financial_withdrawal_status_option()
    {
        $financial_withdrawal_status = config('constants.financial_withdrawal_status_option');
        $financial_withdrawal_status_options = [];
        foreach ($financial_withdrawal_status as $key => $text)
        {
            $financial_withdrawal_status_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $financial_withdrawal_status_options;
    }
}
if (!function_exists('financial_withdrawal_status_option_for_action'))
{
    function financial_withdrawal_status_option_for_action()
    {
        $financial_withdrawal_status = config('constants.financial_withdrawal_status_option_for_action');
        $financial_withdrawal_action_options = [];
        foreach ($financial_withdrawal_status as $key => $text)
        {
            $financial_withdrawal_action_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $financial_withdrawal_action_options;
    }
}
if (!function_exists('financial_withdrawal_status_option_for_transfer'))
{
    function financial_withdrawal_status_option_for_transfer()
    {
        $financial_withdrawal_transfer = config('constants.financial_withdrawal_status_option_for_transfer');
        $financial_withdrawal_transfer_options = [];
        foreach ($financial_withdrawal_transfer as $key => $text)
        {
            $financial_withdrawal_transfer_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $financial_withdrawal_transfer_options;
    }
}
if (!function_exists('bank_transfer_status_options'))
{
    function bank_transfer_status_options()
    {
        $financial_withdrawal_transfer = config('constants.bank_transfer_status_options');
        $financial_withdrawal_transfer_options = [];
        foreach ($financial_withdrawal_transfer as $key => $text)
        {
            $financial_withdrawal_transfer_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $financial_withdrawal_transfer_options;
    }
}
if (!function_exists('transfer_status_options'))
{
    function transfer_status_options()
    {
        $financial_withdrawal_transfer = config('constants.transfer_status_options');
        $financial_withdrawal_transfer_options = [];
        foreach ($financial_withdrawal_transfer as $key => $text)
        {
            $financial_withdrawal_transfer_options[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $financial_withdrawal_transfer_options;
    }
}

if (!function_exists('financial_withdrawal_status_filter'))
{
    function financial_withdrawal_status_filter()
    {
        $financial_withdrawal_status = config('constants.financial_withdrawal_status_filter');
        $financial_withdrawal_status_filter = [];
        foreach ($financial_withdrawal_status as $key => $text)
        {
            $financial_withdrawal_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $financial_withdrawal_status_filter;
    }
}
if (!function_exists('ticket_priority_level_option'))
{
    function ticket_priority_level_option()
    {
        $ticket_priority_level = config('constants.ticket_priority_level_option');
        $ticket_priority_level_filter = [];
        foreach ($ticket_priority_level as $key => $text)
        {
            $ticket_priority_level_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $ticket_priority_level_filter;
    }
}
if (!function_exists('ticket_status_option'))
{
    function ticket_status_option()
    {
        $ticket_status_level = config('constants.ticket_status_option');
        $ticket_status_level_filter = [];
        foreach ($ticket_status_level as $key => $text)
        {
            $ticket_status_level_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $ticket_status_level_filter;
    }
}
if (!function_exists('inventory_apply_option'))
{
    function inventory_apply_option()
    {
        $inventory_action = config('constants.inventory_apply_option');
        $inventory_action_filter = [];
        foreach ($inventory_action as $key => $text)
        {
            $inventory_action_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $inventory_action_filter;
    }
}
if (!function_exists('inventory_archive_option'))
{
    function inventory_archive_option()
    {
        $inventory_archive = config('constants.inventory_archive_option');
        $inventory_archive_filter = [];
        foreach ($inventory_archive as $key => $text)
        {
            $inventory_archive_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $inventory_archive_filter;
    }
}
if (!function_exists('send_type_moadin_option'))
{
    function send_type_moadin_option()
    {
        $inventory_archive = config('constants.send_type_moadin_option');
        $inventory_archive_filter = [];
        foreach ($inventory_archive as $key => $text)
        {
            $inventory_archive_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $inventory_archive_filter;
    }

}
if (!function_exists('submitted_type_moadin_option'))
{
    function submitted_type_moadin_option()
    {
        $inventory_archive = config('constants.submitted_type_moadin_option');
        $inventory_archive_filter = [];
        foreach ($inventory_archive as $key => $text)
        {
            $inventory_archive_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $inventory_archive_filter;
    }
}


if (!function_exists('withdrawal_dc_action_option'))
{
    function withdrawal_dc_action_option()
    {
        $inventory_archive = config('constants.withdrawal_dc_action_option');
        $inventory_archive_filter = [];
        foreach ($inventory_archive as $key => $text)
        {
            $inventory_archive_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $inventory_archive_filter;
    }
}

if (!function_exists('inventory_side_option'))
{
    function inventory_side_option()
    {
        $inventory_side = config('constants.inventory_side_option');
        $inventory_side_filter = [];
        foreach ($inventory_side as $key => $text)
        {
            $inventory_side_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $inventory_side_filter;
    }
}
if (!function_exists('credits_options'))
{
    function credits_options()
    {
        $inventory_side = config('constants.credits_options');
        $inventory_side_filter = [];
        foreach ($inventory_side as $key => $text)
        {
            $inventory_side_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $inventory_side_filter;
    }
}
if (!function_exists('inventory_irr_origin_type_option'))
{
    function inventory_irr_origin_type_option()
    {
        $inventory_origin_type = config('constants.inventory_irr_origin_type_option');
        $inventory_origin_type_filter = [];
        foreach ($inventory_origin_type as $key => $text)
        {
            $inventory_origin_type_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $inventory_origin_type_filter;
    }
}
if (!function_exists('inventory_dc_origin_type_option'))
{
    function inventory_dc_origin_type_option()
    {
        $inventory_origin_type = config('constants.inventory_dc_origin_type_option');
        $inventory_origin_type_filter = [];
        foreach ($inventory_origin_type as $key => $text)
        {
            $inventory_origin_type_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $inventory_origin_type_filter;
    }
}
if (!function_exists('inventory_circulation_account_option'))
{
    function inventory_circulation_account_option()
    {
        $inventory_origin_type = config('constants.inventory_circulation_account_option');
        $inventory_origin_type_filter = [];
        foreach ($inventory_origin_type as $key => $text)
        {
            $inventory_origin_type_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $inventory_origin_type_filter;
    }
}
if (!function_exists('bfu_status_for_action_option'))
{
    function bfu_status_for_action_option()
    {
        $bfu_status = config('constants.bfu_status_for_action_option');
        $bfu_status_filter = [];
        foreach ($bfu_status as $key => $text)
        {
            $bfu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $bfu_status_filter;
    }
}
if (!function_exists('bfu_api_status_option'))
{
    function bfu_api_status_option()
    {
        $bfu_status = config('constants.bfu_api_status_option');
        $bfu_status_filter = [];
        foreach ($bfu_status as $key => $text)
        {
            $bfu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $bfu_status_filter;
    }
}
if (!function_exists('bfu_api_bill_subject_option'))
{
    function bfu_api_bill_subject_option()
    {
        $bfu_status = config('constants.bfu_api_bill_subject_option');
        $bfu_status_filter = [];
        foreach ($bfu_status as $key => $text)
        {
            $bfu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $bfu_status_filter;
    }
}

if (!function_exists('bfu_status_for_action_filter_option'))
{
    function bfu_status_for_action_filter_option()
    {
        $stu_status = config('constants.bfu_status_for_action_filter_option');
        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('invoice_reaction_support_filter_option'))
{
    function invoice_reaction_support_filter_option()
    {
        $stu_status = config('constants.invoice_reaction_support_filter_option');
        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('invoice_reaction_site_filter_option'))
{
    function invoice_reaction_site_filter_option()
    {
        $stu_status = config('constants.invoice_reaction_site_filter_option');
        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('stu_status_for_action_option'))
{
    function stu_status_for_action_option()
    {
        $stu_status = config('constants.stu_status_for_action_option');
        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('stu_status_for_action_filter_option'))
{
    function stu_status_for_action_filter_option()
    {
        $stu_status = config('constants.stu_status_for_action_filter_option');
        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('stu_settlement_type_options'))
{
    function stu_settlement_type_options()
    {
        $stu_settlement_type = config('constants.stu_settlement_type_options');
        $stu_settlement_type_filter = [];
        foreach ($stu_settlement_type as $key => $text)
        {
            $stu_settlement_type_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_settlement_type_filter;
    }
}

if (!function_exists('deposit_type_direct_option'))
{
    function deposit_type_direct_option()
    {
        $stu_status = config('constants.deposit_type_direct_option');
        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('deposit_type_direct_option_for_expert'))
{
    function deposit_type_direct_option_for_expert()
    {
        $stu_status = config('constants.deposit_type_direct_option_for_expert');
        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('deposit_direct_status_option'))
{
    function deposit_direct_status_option()
    {
        $stu_status = config('constants.deposit_direct_status_option');
        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('deposit_direct_action_option'))
{
    function deposit_direct_action_option()
    {
        $stu_status = config('constants.deposit_direct_action_option');
        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('deposit_direct_status_filter_option'))
{
    function deposit_direct_status_filter_option()
    {
        $stu_status = config('constants.deposit_direct_status_filter_option');
        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('dc_status_options'))
{
    function dc_status_options()
    {
        $status = config('constants.dc_status_options');
        $status_filter = [];
        foreach ($status as $key => $text)
        {
            $status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $status_filter;
    }
}
if (!function_exists('notification_color_option'))
{
    function notification_color_option()
    {
        $stu_status = config('constants.notification_color_option');
        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('notification_type_option'))
{
    function notification_type_option()
    {
        $stu_status = config('constants.notification_type_option');
        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('notification_count_show_options'))
{
    function notification_count_show_options()
    {
        $stu_status = config('constants.notification_count_show');

        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key . '',
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('notification_receiver_type_options'))
{
    function notification_receiver_type_options()
    {
        $stu_status = config('constants.notification_receiver_type_options');

        $stu_status_filter = [];
        foreach ($stu_status as $key => $text)
        {
            $stu_status_filter[] =
                [
                    'id'   => $key . '',
                    'text' => $text,
                ];
        }

        return $stu_status_filter;
    }
}
if (!function_exists('dollar_type_options'))
{
    function dollar_type_options()
    {
        $dollar_types = config('constants.dollar_type');

        $dollar_types_filter = [];
        foreach ($dollar_types as $key => $text)
        {
            $dollar_types_filter[] =
                [
                    'id'   => $key . '',
                    'text' => $text,
                ];
        }

        return $dollar_types_filter;
    }
}

if (!function_exists('in_out_type_options'))
{
    function in_out_type_options()
    {
        $in_out_types = config('constants.in_out_type');

        $in_out_types_filter = [];
        foreach ($in_out_types as $key => $text)
        {
            $in_out_types_filter[] =
                [
                    'id'   => $key . '',
                    'text' => $text,
                ];
        }

        return $in_out_types_filter;
    }
}
if (!function_exists('archive_status_options'))
{
    function archive_status_options()
    {
        $archive_statuses = config('constants.archive_status_for_filter');

        $archive_statuses_filter = [];
        foreach ($archive_statuses as $key => $text)
        {
            $archive_statuses_filter[] =
                [
                    'id'   => $key . '',
                    'text' => $text,
                ];
        }

        return $archive_statuses_filter;
    }
}
if (!function_exists('withdrawal_status_options'))
{
    function withdrawal_status_options()
    {
        $archive_statuses = config('constants.withdrawal_status_options');

        $archive_statuses_filter = [];
        foreach ($archive_statuses as $key => $text)
        {
            $archive_statuses_filter[] =
                [
                    'id'   => $key . '',
                    'text' => $text,
                ];
        }

        return $archive_statuses_filter;
    }
}
if (!function_exists('deposit_status_options'))
{
    function deposit_status_options()
    {
        $archive_statuses = config('constants.deposit_status_options');

        $archive_statuses_filter = [];
        foreach ($archive_statuses as $key => $text)
        {
            $archive_statuses_filter[] =
                [
                    'id'   => $key . '',
                    'text' => $text,
                ];
        }

        return $archive_statuses_filter;
    }
}
if (!function_exists('general_status_options'))
{
    function general_status_options()
    {
        $archive_statuses = config('constants.general_status_options');

        $archive_statuses_filter = [];
        foreach ($archive_statuses as $key => $text)
        {
            $archive_statuses_filter[] =
                [
                    'id'   => $key . '',
                    'text' => $text,
                ];
        }

        return $archive_statuses_filter;
    }
}
if (!function_exists('dollar_inventory_origin_type_options'))
{
    function dollar_inventory_origin_type_options()
    {
        $origin_types = config('constants.dollar_inventory_origin_type_options');

        $origin_types_filter = [];
        foreach ($origin_types as $key => $text)
        {
            $origin_types_filter[] =
                [
                    'id'   => $key . '',
                    'text' => $text,
                ];
        }

        return $origin_types_filter;
    }
}
if (!function_exists('authentication_status_chane_reason'))
{
    function authentication_status_chane_reason()
    {
        $authentication_status_chane_reason = config('constants.authentication_status_chane_reason');
        $authentication_status_chane_reason_filter = [];
        foreach ($authentication_status_chane_reason as $key => $text)
        {
            $authentication_status_chane_reason_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $authentication_status_chane_reason_filter;
    }
}
if (!function_exists('status_inactive'))
{
    function status_inactive()
    {
        $authentication_status_chane_reason = config('constants.status_inactive');
        $authentication_status_chane_reason_filter = [];
        foreach ($authentication_status_chane_reason as $key => $text)
        {
            $authentication_status_chane_reason_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $authentication_status_chane_reason_filter;
    }
}
if (!function_exists('status_save_documents'))
{
    function status_save_documents()
    {
        $authentication_status_chane_reason = config('constants.status_save_documents');
        $authentication_status_chane_reason_filter = [];
        foreach ($authentication_status_chane_reason as $key => $text)
        {
            $authentication_status_chane_reason_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $authentication_status_chane_reason_filter;
    }
}


if (!function_exists('type_authentication_options'))
{
    function type_authentication_options()
    {
        $type_authentication_options = config('constants.type_authentication_options');
        $type_authentication_filter = [];
        foreach ($type_authentication_options as $key => $text)
        {
            $type_authentication_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $type_authentication_filter;
    }
}


if (!function_exists('authentication_status'))
{
    function authentication_status()
    {
        $authentication_status = config('constants.authentication_status_status');
        $authentication_status_filter = [];
        foreach ($authentication_status as $key => $text)
        {
            $authentication_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $authentication_status_filter;
    }
}
if (!function_exists('deposit_irr_online_payment_status_for_filter_options'))
{
    function deposit_irr_online_payment_status_for_filter_options()
    {
        $deposit_status = config('constants.deposit_irr_online_payment_status_for_filter_options');
        $deposit_status_filter = [];
        foreach ($deposit_status as $key => $text)
        {
            $deposit_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $deposit_status_filter;
    }
}
if (!function_exists('status_wallet_options'))
{
    function status_wallet_options()
    {
        $deposit_status = config('constants.status_wallet_options');
        $deposit_status_filter = [];
        foreach ($deposit_status as $key => $text)
        {
            $deposit_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $deposit_status_filter;
    }
}

if (!function_exists('deposit_type_tax_options'))
{
    function deposit_type_tax_options()
    {
        $deposit_status = config('constants.deposit_type_tax_options');
        $deposit_status_filter = [];
        foreach ($deposit_status as $key => $text)
        {
            $deposit_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $deposit_status_filter;
    }
}


if (!function_exists('withdrawal_dc_direct_status_option'))
{
    function withdrawal_dc_direct_status_option()
    {
        $inventory_archive = config('constants.withdrawal_dc_direct_status_option');
        $inventory_archive_filter = [];
        foreach ($inventory_archive as $key => $text)
        {
            $inventory_archive_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $inventory_archive_filter;
    }
}

if (!function_exists('p2p_market_chart_language_options'))
{
    function p2p_market_chart_language_options()
    {
        $language_options = config('constants.p2p_market_chart_language_options');
        $language_options_filter = [];

        foreach ($language_options as $key => $text)
        {
            $language_options_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $language_options_filter;
    }
}
if (!function_exists('p2p_market_chart_display_status_options'))
{
    function p2p_market_chart_display_status_options()
    {
        $chart_display_status_options = config('constants.p2p_market_chart_display_status_options');
        $chart_display_status_filter = [];

        foreach ($chart_display_status_options as $key => $text)
        {
            $chart_display_status_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $chart_display_status_filter;
    }
}
if (!function_exists('p2p_market_chart_default_time_interval_options'))
{
    function p2p_market_chart_default_time_interval_options()
    {
        $chart_default_time_interval_options = config('constants.p2p_market_chart_default_time_interval_options');
        $chart_default_time_interval_filter = [];

        foreach ($chart_default_time_interval_options as $key => $text)
        {
            $chart_default_time_interval_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $chart_default_time_interval_filter;
    }
}
if (!function_exists('p2p_market_chart_bar_style_options'))
{
    function p2p_market_chart_bar_style_options()
    {
        $chart_bar_style_options = config('constants.p2p_market_chart_bar_style_options');
        $chart_bar_style_filter = [];

        foreach ($chart_bar_style_options as $key => $text)
        {
            $chart_bar_style_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $chart_bar_style_filter;
    }
}
if (!function_exists('market_status_options'))
{
    function market_status_options()
    {
        $market_status_options = config('constants.market_status_options');
        $market_status_option_filter = [];

        foreach ($market_status_options as $key => $text)
        {
            $market_status_option_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $market_status_option_filter;
    }
}
if (!function_exists('market_order_type_options'))
{
    function market_order_type_options()
    {
        $market_order_type_options = config('constants.market_order_type_options');
        $market_order_type_filter = [];

        foreach ($market_order_type_options as $key => $text)
        {
            $market_order_type_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $market_order_type_filter;
    }
}
if (!function_exists('market_depth_options'))
{
    function market_depth_options()
    {
        $market_depth_options = config('constants.market_depth_options');
        $market_depth_filter = [];

        foreach ($market_depth_options as $key => $text)
        {
            $market_depth_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $market_depth_filter;
    }
}
if (!function_exists('market_depth_options'))
{
    function market_depth_options()
    {
        $market_depth_options = config('constants.market_depth_options');
        $market_depth_filter = [];

        foreach ($market_depth_options as $key => $text)
        {
            $market_depth_filter[] =
                [
                    'id'   => $key,
                    'text' => $text,
                ];
        }

        return $market_depth_filter;
    }
}




