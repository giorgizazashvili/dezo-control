<?php

return [

    'column_manager' => [

        'heading' => 'სვეტები',

        'actions' => [

            'apply' => [
                'label' => 'სვეტების გამოყენება',
            ],

            'reset' => [
                'label' => 'თავიდან დაყენება',
            ],

        ],

    ],

    'columns' => [

        'actions' => [
            'label' => 'მოქმედება|მოქმედებები',
        ],

        'select' => [

            'loading_message' => 'იტვირთება...',

            'no_options_message' => 'არჩევანი არ არის.',

            'no_search_results_message' => 'ძიებას შედეგი არ მოაქვს.',

            'placeholder' => 'აირჩიეთ',

            'searching_message' => 'ძიება მიმდინარეობს...',

            'search_prompt' => 'ძიებისთვის აკრიფეთ...',

        ],

        'text' => [

            'actions' => [
                'collapse_list' => ':count-ით ნაკლების ჩვენება',
                'expand_list' => 'კიდევ :count-ის ჩვენება',
            ],

            'more_list_items' => 'და კიდევ :count',

        ],

    ],

    'fields' => [

        'bulk_select_page' => [
            'label' => 'ყველა ჩანაწერის მონიშვნა/მოხსნა მასობრივი მოქმედებებისთვის.',
        ],

        'bulk_select_record' => [
            'label' => ':key ჩანაწერის მონიშვნა/მოხსნა მასობრივი მოქმედებებისთვის.',
        ],

        'bulk_select_group' => [
            'label' => ':title ჯგუფის მონიშვნა/მოხსნა მასობრივი მოქმედებებისთვის.',
        ],

        'search' => [
            'label' => 'ძიება',
            'placeholder' => 'ძიება',
            'indicator' => 'ძიება',
        ],

    ],

    'summary' => [

        'heading' => 'შეჯამება',

        'subheadings' => [
            'all' => 'ყველა :label',
            'group' => ':group შეჯამება',
            'page' => 'ეს გვერდი',
        ],

        'summarizers' => [

            'average' => [
                'label' => 'საშუალო',
            ],

            'count' => [
                'label' => 'რაოდენობა',
            ],

            'sum' => [
                'label' => 'ჯამი',
            ],

        ],

    ],

    'actions' => [

        'disable_reordering' => [
            'label' => 'დალაგების დასრულება',
        ],

        'enable_reordering' => [
            'label' => 'ჩანაწერების გადალაგება',
        ],

        'filter' => [
            'label' => 'ფილტრი',
        ],

        'group' => [
            'label' => 'დაჯგუფება',
        ],

        'open_bulk_actions' => [
            'label' => 'მასობრივი მოქმედებები',
        ],

        'column_manager' => [
            'label' => 'სვეტების მართვა',
        ],

    ],

    'empty' => [

        'heading' => ':model არ მოიძებნა',

        'description' => 'შექმენით :model დასაწყებად.',

    ],

    'filters' => [

        'actions' => [

            'apply' => [
                'label' => 'ფილტრების გამოყენება',
            ],

            'remove' => [
                'label' => 'ფილტრის მოხსნა',
            ],

            'remove_all' => [
                'label' => 'ყველა ფილტრის მოხსნა',
                'tooltip' => 'ყველა ფილტრის მოხსნა',
            ],

            'reset' => [
                'label' => 'თავიდან დაყენება',
            ],

        ],

        'heading' => 'ფილტრები',

        'indicator' => 'აქტიური ფილტრები',

        'multi_select' => [
            'placeholder' => 'ყველა',
        ],

        'select' => [

            'placeholder' => 'ყველა',

            'relationship' => [
                'empty_option_label' => 'არცერთი',
            ],

        ],

        'trashed' => [

            'label' => 'წაშლილი ჩანაწერები',

            'only_trashed' => 'მხოლოდ წაშლილი ჩანაწერები',

            'with_trashed' => 'წაშლილი ჩანაწერებით',

            'without_trashed' => 'წაშლილი ჩანაწერების გარეშე',

        ],

    ],

    'grouping' => [

        'fields' => [

            'group' => [
                'label' => 'დაჯგუფება',
            ],

            'direction' => [

                'label' => 'დაჯგუფების მიმართულება',

                'options' => [
                    'asc' => 'ზრდადი',
                    'desc' => 'კლებადი',
                ],

            ],

        ],

    ],

    'reorder_indicator' => 'გადაათრიეთ ჩანაწერები სასურველ თანმიმდევრობაში.',

    'selection_indicator' => [

        'selected_count' => '1 ჩანაწერი არჩეულია|:count ჩანაწერი არჩეულია',

        'actions' => [

            'select_all' => [
                'label' => 'ყველას არჩევა (:count)',
            ],

            'deselect_all' => [
                'label' => 'ყველას მოხსნა',
            ],

        ],

    ],

    'sorting' => [

        'fields' => [

            'column' => [
                'label' => 'დალაგება',
            ],

            'direction' => [

                'label' => 'დალაგების მიმართულება',

                'options' => [
                    'asc' => 'ზრდადი',
                    'desc' => 'კლებადი',
                ],

            ],

        ],

    ],

    'default_model_label' => 'ჩანაწერი',

];
