<?php
namespace jlawrence\wowApi;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of wowstats
 *
 * @author Jon Lawrence
 */
class Stats {
    static public function popularTitles() {
        $site = Core::$sSite;
        $query = 'select distinct wc.titleId, wc.titleFormat, count(distinct wa.id) as numPeople from '. $site->pdo->TP .'wowCharacters as wc LEFT JOIN '.$site->pdo->TP.'wowCharacters as wa ON wc.titleId = wa.titleId GROUP BY wc.titleId ORDER BY numPeople DESC';
        $ret = $site->pdo->query($query, null);
        return $ret;
    }
}
