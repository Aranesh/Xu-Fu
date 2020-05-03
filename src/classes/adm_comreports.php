<?php

// =======================================================================================================
// ================================== ?????? BACKEND ??????? =============================================
// =======================================================================================================
// ================================== ?????? FRONTEND ?????? =============================================
// =======================================================================================================

?>
<div class="blogtitle">

<table width="100%" border="0" margin="0" cellpadding="0" cellspacing="0">
<tr>
<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>
<td>
    <img class="ut_icon" width="84" height="84" <? echo $usericon ?>>
</td>

<td>
    <img src="images/blank.png" width="50" height="1" alt="" />
</td>

<td width="100%"><h class="megatitle">Administration - Comment Reports</h></td>
<td><img src="images/main_bg02_2.png"></td>
</tr>
</table>

</div>



<div class="remodal-bg leftmenu">
    <?
    print_profile_menu('admin');
    ?>
</div>




<div class="blogentryfirst">
<div class="articlebottom">
</div>

<table style="width: 100%;">
    <tr>
        <td width="1%">
            <img src="images/blank.png" width="250" height="1">
        </td>
            <td>




<table style="width: 100%;">
<tr>
<td>
<table style="width: 85%;" class="profile">

    <? print_admin_menu('adm_comreports'); ?>

    <tr style="background: #bcbcbc; border: 1px solid #bcbcbc;">
        <td class="profile">
            <?
                include_once ('classes/Database.php');

                $reportsdb = Database_query ( "SELECT Reports.* "
                        . "FROM Reports "
                        . "ORDER BY id ASC"
                          );
                
                
            $reportscounter = "0";
            
            while ($this_report = mysqli_fetch_object($reportsdb)) {
                $reports[$reportscounter]['ID'] = $this_report->id;
                $reports[$reportscounter]['Comment'] = $this_report->SortingID;
                $reports[$reportscounter]['User'] = $this_report->User;
                if ($this_report->Type == 0) {
                    $reports[$reportscounter]['Type'] = "Inappropriate";
                }
                if ($this_report->Type == 1) {
                    $reports[$reportscounter]['Type'] = "Spam";
                }
                if ($this_report->Type == 2) {
                    $reports[$reportscounter]['Type'] = "Other";
                }
                $reports[$reportscounter]['Note'] = $this_report->Content;
                if ($this_report->Reviewed == 0) {
                    $reports[$reportscounter]['Reviewed'] = "No";
                }
                if ($this_report->Reviewed == 1) {
                    $reports[$reportscounter]['Reviewed'] = "Yes";
                }
                $reportscounter++; 
            }
            
            sortBy('id', $reports, 'desc');

            ?>
            <table width="100%" id="t1" style="border-collapse: collapse;" class="example table-autosort table-autofilter table-autopage:30 table-page-number:t1page table-page-count:t1pages table-filtered-rowcount:t1filtercount table-rowcount:t1allcount">
            <thead>
                <tr>
                    <th align="left" class="admin petlistheaderfirst"></th>
                    <th align="left" class="admin petlistheaderfirst"></th>
                    <th align="left" class="admin petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 15px;">Type</p></th>
                    <th align="left" class="admin petlistheaderfirst"></th>
                    <th align="left" class="admin petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black">Reviewed</p></th>
                </tr>
                <tr>
                    <th align="left" class="admin petlistheaderfirst table-sortable:alphabetic" width="55"><center><p class="table-sortable-black">Link</p></center></th>
                    <th align="left" class="admin petlistheaderfirst table-sortable:alphabetic" width="80"><center><p class="table-sortable-black">User</p></center></th>
                    <th align="left" class="admin petlistheaderfirst table-sortable:alphabetic" width="80">
                        <select class="petselect" onchange="Table.filter(this,this);">
                        <option class="petselect" value="">All</option>
                        <option class="petselect" value="Spam">Spam</option>
                        <option class="petselect" value="Inappropriate">Inappropriate</option>
                        <option class="petselect" value="Other">Other</option>
                       </select>
                    </th>
                    <th align="left" class="admin petlistheaderfirst table-sortable:alphabetic"><p class="table-sortable-black" style="margin-left: 15px;">Note</p></th>
                    <th align="left" class="admin petlistheaderfirst table-sortable:alphabetic" width="80">
                        <select class="petselect" id="reviewedFilter" onchange="Table.filter(this,this);">
                        <option class="petselect" value="">All</option>
                        <option class="petselect" value="Yes">Yes</option>
                        <option class="petselect" value="No" selected>No</option>
                       </select>
                    </th>
                </tr>
            </thead>
            <tbody>

            <?php
            foreach($reports as $key => $value) {  ?>

                <tr class="admin">
                    <td class="admin"><center><a class="pr_contact" target="_blank" href="?Comment=<? echo $value['Comment'] ?>"><? echo $value['Comment'] ?></a></td>
                    <td class="admin">
                        <center><span style="text-decoration: none" class="username" rel="<? echo $value['User'] ?>" value="<? echo $user->id ?>"><a target="_blank" href="?user=<? echo $value['User'] ?>" class="pr_contact"><? echo $value['User'] ?></a></span>  
                    </td>
                    <td class="admin"><? echo $value['Type'] ?></td>
                    <td class="admin" style="min-width: 300px"><? echo $value['Note'] ?></td>
                    <td class="admin"><center><? echo $value['Reviewed'] ?></center></td>

            </tr>
            <?php
            }
            ?>

            </tbody>
            <tfoot>
                    <tr>
                            <td colspan="2" style="padding-top: 10px" align="right" class="table-page:previous" style="padding-top: 5px;"><a class="wowhead" style="cursor:pointer;text-decoration: none;">&lt; &lt; </a></td>
                            <td colspan="1" style="padding-top: 10px" align="center"><div style="white-space:nowrap;padding-top: 5px;"><p class="blogodd"><span id="t1page"></span> / <span id="t1pages"></span></div></td>
                            <td colspan="2" style="padding-top: 10px" align="left" class="table-page:next" style="padding-top: 5px;"><a class="wowhead" style="cursor:pointer;text-decoration: none;"> &gt; &gt;</td>
                    </tr>
            </tfoot>
        </table>
        <br>
        </td>
    </tr>

</table>

</table>


<script>
    Table.filter(this.document.getElementById('reviewedFilter'),this.document.getElementById('reviewedFilter'));
</script>




</td>
</tr>
</table>

<br><br><br><br><br><br>

</div>

<?
mysqli_close($dbcon);
echo "</body>";
die;