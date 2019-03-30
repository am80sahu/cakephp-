<?php

/**
 * @author Ashish Tiwari
 *
 */
App::uses('Component', 'Controller');

class DateComponent extends Component {

    function db_dt_format($date) {

        return date(DBDATEFORMAT, strtotime($date));
    }

    function db_time_format($time) {

        return date(DBTIMEFORMAT, strtotime($time));
    }

    function db_dt_time_format($date) {

        return date(DBDATETIMEFORMAT, strtotime($date));
    }

    function simple_dt_format($date) {

        return date(DATEFORMAT, strtotime($date));
    }

    function fancy_dt_format($date) {

        return date(FANCYDATEFORMAT, strtotime($date));
    }

    function simple_dt_time_format($date) {

        return date(DATETIMEFORMAT, strtotime($date));
    }

    function fancy_dt_time_format($date) {

        return date(FANCYDATETIMEFORMAT, strtotime($date));
    }

    function numeric_month($date) {

        return date(NUMERICMONTH, strtotime($date));
    }

}
