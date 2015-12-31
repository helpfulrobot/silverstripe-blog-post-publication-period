<?php

/* 
 * @author Daniele Manassero <daniele.manassero@gmail.com>
 * @creation-date 09/07/2014
 */

class AdminPublicationPostDate extends DataExtension
{
    
    private static $db = array(
        "FromDate" => "Date",
        "ToDate" => "Date",
    );
    
    public function updateCMSFields(\FieldList $fields)
    {
        parent::updateCMSFields($fields);
        
        $field = new DateField('FromDate');
        $field->setConfig('showcalendar', true);
        $field->setConfig('dateformat', 'dd/MM/YYYY');
        $field->setTitle(_t("BlogEntry.FROMDT", "Date"));
        if ($this->owner->Created == $this->owner->LastEdited) {
            // In caso di nuovo post, setto il campo FromDate ad oggi
            $now = Zend_Date::now();
            $this->owner->FromDate = $now->toString('d/M/Y');
        }
        $fields->addFieldToTab('Root.PublishedPeriod', $field);
        
        $field = new DateField('ToDate');
        $field->setConfig('showcalendar', true);
        $field->setConfig('dateformat', 'dd/MM/YYYY');
        $field->setTitle(_t("BlogEntry.TODT", "Date"));
        $fields->addFieldToTab('Root.PublishedPeriod', $field);
    }
}


class FilterByDateBlogTree extends Extension
{
    
    /**
    * Determine selected BlogEntry items to show on this page
    * 
    * @param int $limit
    * @return PaginatedList
    */
    public function FilteredBlogEntries($limit = null)
    {
        require_once('Zend/Date.php');

        if ($limit === null) {
            $limit = BlogTree::$default_entries_limit;
        }

        // only use freshness if no action is present (might be displaying tags or rss)
        if ($this->owner->LandingPageFreshness && !$this->owner->request->param('Action')) {
            $d = new Zend_Date(SS_Datetime::now()->getValue());
            $d->sub($this->owner->LandingPageFreshness, Zend_Date::MONTH);
            $date = $d->toString('YYYY-MM-dd');

            $filter = "\"BlogEntry\".\"Date\" > '$date'";
        } else {
            $filter = '';
        }
        // allow filtering by author field and some blogs have an authorID field which
        // may allow filtering by id
        if (isset($_GET['author']) && isset($_GET['authorID'])) {
            $author = Convert::raw2sql($_GET['author']);
            $id = Convert::raw2sql($_GET['authorID']);

            $filter .= " \"BlogEntry\".\"Author\" LIKE '". $author . "' OR \"BlogEntry\".\"AuthorID\" = '". $id ."'";
        } elseif (isset($_GET['author'])) {
            $filter .=  " \"BlogEntry\".\"Author\" LIKE '". Convert::raw2sql($_GET['author']) . "'";
        } elseif (isset($_GET['authorID'])) {
            $filter .=  " \"BlogEntry\".\"AuthorID\" = '". Convert::raw2sql($_GET['authorID']). "'";
        }

        $date = $this->owner->SelectedDate();

        return $this->owner->Entries($limit, $this->owner->SelectedTag(), ($date) ? $date : '', array(get_class($this), 'FilterByDate'), $filter);
    }
   
   /*
    * 
    */
   public static function FilterByDate($class, $filter, $limit, $order)
   {
       $filter .= ' AND (CURDATE() >= FromDate AND (CURDATE() <= ToDate || ToDate IS NULL))';
        
       $entries = $class::get()->where($filter)->sort($order);
        
       $list = new PaginatedList($entries, Controller::curr()->request);
       $list->setPageLength($limit);
       return $list;
   }
}
