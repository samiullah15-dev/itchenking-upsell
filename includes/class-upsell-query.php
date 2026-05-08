<?php

class ItchenKing_Upsell_Query {

    public static function get_products($remaining, $exclude = []) {

        $args = [

            'status'  => 'publish',
            'limit'   => 12,
            'exclude' => $exclude,
            'orderby' => 'date',
            'order'   => 'DESC',
        ];

        return wc_get_products($args);
    }
}