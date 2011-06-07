<?php
/**
 * class Visitor_Model_Resource_Visitor_Collection
 * 
 * @package Visitor
 * @category Resource-Collection-Model
 * @copyright Copyright (c) 2010 Hettema&Bergsten
 * @author      
 */
class Visitor_Model_Resource_Visitor_Collection extends Core_Model_Resource_Collection_Abstract
{
    protected $tbl_visitor = 'log_visitor';
    protected $tbl_visitor_info = 'log_visitor_info';
    protected $tbl_url = 'log_url';
    protected $tbl_url_info = 'log_url_info';

    public function _construct()
    {
        $this->filters = array( 'date_from'=>date('Y-m-d'),
                                'date_to' => false,
                                'browser' => false,
                                'os' => false,
                                'url' => false,
                                'redirect_source' => false,
                                'hide_me' => false,
                                'no_robots' => false,
                                'group_ip' => false,
                                'hide_proxy' => false,
                                'hide_proxy_ping' => false,
                                'show_fb_redirects' => false,
                                'show_blogger_redirects' => false,
                                'min_page_visit' => false,
                                'page' => 1,
                                'limit' => 100,
                                'order' => 'DESC',
                                'order_by' => 'visitor_id',
                                'group_by' => 'visitor_id',
                                );

        $this->_getQuery()->setCountColumn('visitor.visitor_id');
        $this->setFilters();
    }

    /**
     * Retrive the visitors list filterd
     * 
     * @return Core_Model_Object 
     */
    public function getResultCollection()
    {
        $query = $this->_getQuery();
        $query->resetQuery();
        $query->queryColumn(array("visitor.visitor_id",
                                  "count(DISTINCT(visitor.visitor_id)) AS visits", /*used when using with ip grouping*/
                                  "visitor_info.http_referer AS referer",
                                  "visitor_info.remote_addr AS ip_address",
                                  "UNIX_TIMESTAMP(MIN(visitor.first_visit_at)) AS first_visit_ts",
                                  "count(url.url_id) AS total_visits",
                                  "UNIX_TIMESTAMP(MAX(visitor.last_visit_at)) AS last_visit_ts",
                                  "TIMEDIFF(visitor.last_visit_at,visitor.first_visit_at) AS time_on_site",
                                  "visitor_info.http_user_agent"));

        foreach ($this->filters as $filter=>$value) {
            if(empty($value)) continue;

            switch ($filter)
            {
                case 'date_from':
                    $query->queryCondition("visitor.first_visit_at >= '" . $value . "' ");
                break;
                case 'date_to':
                    $query->queryCondition("visitor.first_visit_at <= '" . $value . " 23:59:59' ");
                break;
                case 'no_robots':
                    $query->queryCondition("(visitor_info.http_user_agent NOT LIKE '%googlebot%' AND visitor_info.http_user_agent NOT LIKE '%AdsBot-Google%' AND visitor_info.http_user_agent NOT LIKE '%bingbot%' AND visitor_info.http_user_agent NOT LIKE '%Yahoo! Slurp%' AND visitor_info.http_user_agent NOT LIKE '%baiduspider%' AND visitor_info.http_user_agent NOT LIKE '%ia_archiver%' AND visitor_info.http_user_agent NOT LIKE '%Twiceler%' AND visitor_info.http_user_agent NOT LIKE '%pingdom.com_bot%' AND visitor_info.http_user_agent NOT LIKE '%Toata%' AND  visitor_info.http_user_agent NOT LIKE '%www.seoprofiler.com%') ");
                break;
                case 'min_page_visit':
                    $query->queryCondition("count(url.url_id) >= '" . $value . "' ", 'HAVING');
                break;
                case 'browser':
                    $query->queryCondition("visitor_info.http_user_agent LIKE '%" . $value . "%' ");
                break;
                case 'os':
                    $query->queryCondition("visitor_info.http_user_agent LIKE '%" . $value . "%' ");
                break;
                case 'url':
                    $query->queryCondition("url_info.url LIKE '%" . $value . "%' ");
                break;
                case 'redirect_source':
                    $query->queryCondition("visitor_info.http_referer LIKE '%" . $value . "%' ");
                break;
                case 'hide_me':
                    $query->queryCondition("visitor_info.remote_addr != '" . ip2long($_SERVER['REMOTE_ADDR']) . "' ");
                break;
                case 'hide_proxy':
                    $query->queryCondition("(visitor_info.remote_addr != '" . ip2long('10.206.59.239') . "' AND visitor_info.remote_addr != '" . ip2long('174.129.106.25') . "')");
                break;
                case 'hide_proxy_ping':
                    $query->queryCondition("url_info.url NOT LIKE '%test.html%' ");
                break;
                case 'show_fb_redirects':
                    $query->queryCondition("visitor_info.http_referer LIKE '%facebook.com%' ");
                break;
                case 'show_blogger_redirects':
                    $query->queryCondition("url_info.url LIKE '%?b=%' ");
                break;

                case 'page':
                    if(!empty($this->filters['limit'])) {
                        $query->queryCondition($this->filters['limit'] * ($value - 1) . "," . $this->filters['limit'], 'LIMIT');
                    }
                break;


                case 'group_by':
                    switch (strtolower($this->filters['group_by']))
                    {
                        case 'ip':
                            $query->queryCondition('visitor_info.remote_addr', 'GROUP');
                        break;
                        case 'visitor_id':
                        default:
                            $query->queryCondition('visitor.visitor_id', 'GROUP');
                        break;
                    }
                break;

                case 'order_by':
                    switch (strtolower($this->filters['order_by']))
                    {
                        case 'visitor_id':
                            $query->queryCondition("visitor.visitor_id ". $this->filters['order'], 'ORDER');
                        break;
                        case 'totalvisit':
                            $query->queryCondition("count(url.url_id) ". $this->filters['order'], 'ORDER');
                        break;
                        case 'httpuseragent':
                            $query->queryCondition("visitor_info.http_user_agent ". $this->filters['order'], 'ORDER');
                        break;
                        case 'lastvisit':
                            $query->queryCondition("visitor.last_visit_at ". $this->filters['order'], 'ORDER');
                        break;
                        case 'firstvisit':
                            $query->queryCondition("visitor.first_visit_at ". $this->filters['order'], 'ORDER');
                        break;
                        case 'timeonsite':
                            $query->queryCondition("TIMEDIFF(visitor.last_visit_at,visitor.first_visit_at) ". $this->filters['order'], 'ORDER');
                        break;
                    }
                break;
            }
        }

        $query->queryTable($this->tbl_visitor . " AS visitor");
        $query->queryTable("LEFT JOIN ". $this->tbl_visitor_info . " AS visitor_info USING(visitor_id)");
        $query->queryTable("LEFT JOIN ". $this->tbl_url . " AS url ON url.visitor_id = visitor.visitor_id");
        $query->queryTable("LEFT JOIN ". $this->tbl_url_info . " AS url_info ON url_info.url_id = url.url_id");

        $read = $this->_getResource()->_getReadAdapter();

        $count = $read->fetchOne($query->prepareCountQuery(), 'count');
        $results = $read->fetchAll($query->prepareQuery());

        $collection = array();
        if(!empty($results)) {
            foreach ($results as $result) {
                $collection[] = App_Main::getModel('core/object', $result);
            }
        }

        $resultCollection = new Core_Model_Object();
        $resultCollection->setCollection($collection);
        $resultCollection->setTotalCount($count);
        $resultCollection->setFilters(App_Main::getModel('core/object' ,$this->filters));
        if($this->getFilterValue('page')) {
            $resultCollection->setPage($this->getFilterValue('page'));
            $resultCollection->setLimit($this->getFilterValue('limit'));
        }
        return $resultCollection;
    }

    /**
     *
     * @param intiger $visitorId
     * @param bool $groupByIp
     * @return array  
     */
    public function getVisitorUrls($visitorId, $groupByIp = false)
    {
        if($groupByIp) {
            $cond = "visitor_info.remote_addr = (SELECT DISTINCT(remote_addr) FROM " . $this->tbl_visitor_info . " WHERE visitor_id = '" . $visitorId . "')";
        } else {
            $cond = "visitor_info.visitor_id = '" . $visitorId . "'";
        }
        
        $_sql = "SELECT
                    visitor_info.http_referer,
                    url_info.url,
                    UNIX_TIMESTAMP(url.visit_time) AS visit_time
                 FROM
                 " . $this->tbl_visitor . " AS visitor
                        LEFT JOIN
                 " . $this->tbl_visitor_info . " AS visitor_info USING(visitor_id)
                        LEFT JOIN
                 " . $this->tbl_url . " AS url ON url.visitor_id = visitor.visitor_id
                        LEFT JOIN
                 " . $this->tbl_url_info . " AS url_info ON url_info.url_id = url.url_id
                 WHERE
                    url_info.url IS NOT NULL AND
                 " . $cond . "
                 ORDER BY
                    DATE(url.visit_time) DESC,
                    url.url_id";
        $results =  $this->_getReadAdapter()->fetchAll($_sql);
        return $results;
    }
}
