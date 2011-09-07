<?php
/**
 *   https://09source.kicks-ass.net:8443/svn/installer09/
 *   Licence Info: GPL
 *   Copyright (C) 2010 Installer09 v.2
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn,kidvision.
 **/
if(!defined('IN_LOTTERY'))
  die('You can\'t access this file directly!');

ini_set('display_errors',1);

//get config from database 
$lconf = sql_query('SELECT * FROM lottery_config') or sqlerr(__FILE__,__LINE__);
while($ac = mysql_fetch_assoc($lconf))
  $lottery_config[$ac['name']] = $ac['value'];


if(!$lottery_config['enable'])
  stderr('Sorry','Lottery is closed');

$html = begin_main_frame().begin_frame('Lottery stats');
$html .= "<h2>Lottery started on <b>".get_date($lottery_config['start_date'],'LONG')."</b> and ends on <b>".get_date($lottery_config['end_date'],'LONG')."</b> remaining <span style='color:#ff0000;'>".mkprettytime($lottery_config['end_date']-TIME_NOW)."</span></h2>";

$qs = sql_query('SELECT count(t.id) as tickets , u.username, u.id, u.seedbonus FROM tickets as t LEFT JOIN users as u ON u.id = t.user GROUP BY u.id ORDER BY tickets DESC, username ASC') or sqlerr(__FILE__,__LINE__);
if(!mysql_num_rows($qs))
$html .= "<h2>Not tickets were bought</h2>";
else {
  $html .= "<table width='80%' cellpadding='5' cellspacing='0' border='1' align='center'>
    <tr>
      <td width='100%'>Username</td>
      <td style='white-space:nowrap;'>tickets</td>
      <td style='white-space:nowrap;'>seedbonus</td>
    </tr>";
    while($ar = mysql_fetch_assoc($qs))
      $html .= "<tr>
                  <td align='left'><a href='userdetails.php?id={$ar['id']}'>{$ar['username']}</a></td>
                  <td align='center'>{$ar['tickets']}</td>
                  <td align='center'>{$ar['seedbonus']}</td>
        </tr>";
  $html .= "</table>";
}
  $html .=end_frame().end_main_frame();
  print(stdhead('Lottery tickets').$html.stdfoot());
?>