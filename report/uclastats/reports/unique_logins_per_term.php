<?php
/**
 * Report to get the total number of course sites for a given term.
 *
 * @package    report
 * @subpackage uclastats
 * @copyright  UC Regents
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/local/ucla/lib.php');
require_once($CFG->dirroot . '/report/uclastats/locallib.php');

class unique_logins_per_term extends uclastats_base {
    /**
     * Instead of counting results, return a sumarized result.
     *
     * @param array $results
     * @return string
     */
    public function format_cached_results($results) {
        if (!empty($results)) {
            $result = array_pop($results);
            $stats = new stdClass();
            $stats->day = $result['per_day'];
            $stats->week = $result['per_week'];
            $stats->term = $result['per_term'];
            return get_string('unique_logins_per_term_cached_results',
                    'report_uclastats', $stats);
        }
        return get_string('nocachedresults', 'report_uclastats');
    }

    /**
     * Returns an array of form elements used to run report.
     */
    public function get_parameters() {
        return array('term');
    }

    /**
     * Returns start/end times for a given term.
     *
     * @return array   Returns array with start/end times in unix timestamp.
     *                 Returns null on error.
     */
    private function get_term_info($term) {
        // We need to query the registrar
        ucla_require_registrar();

        $results = registrar_query::run_registrar_query('ucla_getterms',
                    array($term));

        if (empty($results)) {
            return null;
        }

        $ret_val = array();

        // Get ther term start and term end, if it's a summer session,
        // then get start and end of entire summer
        foreach($results as $r) {
            if($r['session'] == 'RG') {
                $ret_val['start'] = strtotime($r['session_start']);
                $ret_val['end'] = strtotime($r['session_end']);
                break;
            } else if($r['session'] == '8A') {
                $ret_val['start'] = strtotime($r['session_start']);
            } else if($r['session'] == '6C') {
                $ret_val['end'] = strtotime($r['session_end']);
            }
        }

        return $ret_val;
    }

    /**
     * Query for average and total unique logins per term
     *
     * @param array $params
     * @param return array
     */
    public function query($params) {
        global $DB;

        $ret_val = array();

        // make sure that term parameter exists
        if (!isset($params['term']) ||
                !ucla_validator('term', $params['term'])) {
            throw new moodle_exception('invalidterm', 'report_uclastats');
        }

        // get start and end dates for term
        $term_info = $this->get_term_info($params['term']);

        // count average unique logins per day (using derived tables! slow, but
        // a way to do all the calculation in the DB layer)
        $sql = "SELECT      ROUND(AVG(login_count))
                FROM        (
                    SELECT  DAYOFYEAR(FROM_UNIXTIME(l.time)) AS day,
                            COUNT(DISTINCT userid) AS login_count
                    FROM        {log} AS l
                    WHERE       l.time >= :start AND
                                l.time <= :end
                    GROUP BY    day
                ) AS logins_per_day";
        $per_day = $DB->get_field_sql($sql, $term_info);
        $ret_val['per_day'] = $per_day;

        // count average unique logins per week (using derived tables! slow, but
        // a way to do all the calculation in the DB layer)
        $sql = "SELECT      ROUND(AVG(login_count))
                FROM        (
                    SELECT  WEEKOFYEAR(FROM_UNIXTIME(l.time)) AS week,
                            COUNT(DISTINCT userid) AS login_count
                    FROM        {log} AS l
                    WHERE       l.time >= :start AND
                                l.time <= :end
                    GROUP BY    week
                ) AS logins_per_week";
        $per_week = $DB->get_field_sql($sql, $term_info);
        $ret_val['per_week'] = $per_week;

        // count the number of unique logins for the term
        $sql = "SELECT  COUNT(DISTINCT userid)
                FROM        {log} AS l
                WHERE       l.time >= :start AND
                            l.time <= :end";
        $per_term = $DB->get_field_sql($sql, $term_info);
        $ret_val['per_term'] = $per_term;

        // might be useful to display the start/end times used
        $ret_val['start_end_times'] = sprintf('%s/%s',
                date('M j, Y', $term_info['start']),
                date('M j, Y', $term_info['end']));

        // the base class is expecting an array of arrays
        return array($ret_val);
    }
}