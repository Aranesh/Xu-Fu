// Xu-Fu's JS Functions

function updateAllTimes(name) {
    var list = document.getElementsByName(name);
    for(var i=0; i<list.length; i++)
      // alert(list[i].innerHTML);
      // var d = list[i].innerHTML.replace(/-/g, '/');
      list[i].innerHTML = new Date(list[i].innerHTML.replace(/-/g, '/')).toLocaleDateString();
}

function scrollto(anchor) {
    $('#'+anchor).removeClass('fademedium');
    $('#'+anchor).addClass('fademedium');
    $('html, body').animate({
        scrollTop: $('#'+anchor).offset().top-20
    }, 800);
}

function load_petlist(main, user, lng, type=0, options=0) {
 $('#pet_table_buttons').hide('0');
 $('#loading_field').show('0');
 $('#pet_table').load('classes/ajax/load_petlist.php?main='+main+'&user='+user+'&lng='+lng+'&type='+type+'&options='+options);
}


function remove_image(id) {
 var r = confirm("Are you sure you want to delete the current picture and replace it with the default image?");
 if (r == true) {

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
     if (this.responseText == 'NOK') {
       $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
     }
     if (this.responseText == 'OK') {
       $('#remove_img_button').hide(0);
       document.getElementById('title_image').src = "images/news/news_default.jpg?" + new Date().getTime();
     }
     
    }
    if (this.status != 200 && this.status != 0 && this.readyState != 4){
     $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
  };
  xmlhttp.open("GET", "classes/ajax/news_remove_image.php?id=" + encodeURIComponent(id), true);
  xmlhttp.send(); 
 }
}


function delete_news_article(id) {
 var r = confirm("Are you sure you want to delete this article?");
 if (r == true) {
  var t = confirm("Really? There is no going back! It'll be gone forever!");
  if (t == true) {

   var xmlhttp = new XMLHttpRequest();
   xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
     if (this.responseText == 'NOK') {
       $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
     }
     if (this.responseText == 'OK') {
      window.location = "https://wow-petguide.com";
     }
     
    }
    if (this.status != 200 && this.status != 0 && this.readyState != 4){
     $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
  };
  xmlhttp.open("GET", "classes/ajax/news_delete_article.php?id=" + encodeURIComponent(id), true);
  xmlhttp.send();  
  
  }
 }
}


// ADMIN - Save menu navigation - adm_menu.php

function save_nav_menu(u,c) {
 var count = 0;
 var pages = [];
 var parentStack = [];

 var result = {};

 parentStack.push(0);

 function createNewLevel(obj) {
     var obj = obj || document.getElementById('nav_menu');

     if (obj.tagName == 'LI') {
         ++count;
         pages.push({
             MenuID: obj.id,
             ParentID: parentStack[parentStack.length - 1],
             MyID: count
         });
     }

     if (obj.hasChildNodes()) {
         var child = obj.firstChild;
         while (child) {
             if (child.nodeType === 1) {

                 if (child.tagName == 'UL') {
                     parentStack.push(count);
                 }

                 createNewLevel(child);

                 if (child.tagName == 'UL') {
                     parentStack.pop();
                 }
             }
             child = child.nextSibling;
         }
     }
 }
createNewLevel();
var output = "";
pages.forEach(function (menuItem) {
 output = output+menuItem.MenuID+','+menuItem.ParentID+','+menuItem.MyID+'-';
});

var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			if (this.responseText != 'OK') {
				$.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
			}
			if (this.responseText == 'OK') {
    $.growl.notice({ message: "Menu saved", duration: "3000", size: "large", location: "tc" });
   }
  }
		if (this.status != 200 && this.status != 0 && this.readyState != 4){
			$.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
		}
	};
	xmlhttp.open("GET", "classes/ajax/adm_save_navmenu.php?menustring=" + encodeURIComponent(output)
	+ "&userid=" + encodeURIComponent(u)
	+ "&delimiter=" + encodeURIComponent(c), true);
	xmlhttp.send(); 
}


// ADMIN - toggle a menu item active or inactive

function adm_menu_toggle_active(lineid,u,c) {
 var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
   if (this.readyState == 4 && this.status == 200) {
    if (this.responseText == 'ON') {
     document.getElementById('adm_op_line_'+lineid).style.opacity = '1';
     document.getElementById('adm_eye_icon_'+lineid).src = "images/icon_eye_open.png";
    }
    if (this.responseText == 'OFF') {
     document.getElementById('adm_op_line_'+lineid).style.opacity = '0.35';
     document.getElementById('adm_eye_icon_'+lineid).src = "images/icon_eye_closed.png";
     
    }
   }
   if (this.status != 200 && this.status != 0 && this.readyState != 4){
    $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
   }
  };
  xmlhttp.open("GET", "classes/ajax/adm_menu_toggle_active.php?lineid=" + encodeURIComponent(lineid)
  + "&userid=" + encodeURIComponent(u)
  + "&delimiter=" + encodeURIComponent(c), true);
  xmlhttp.send(); 
}

// Save tag priority settings

function save_tag_settings(u,c) {
 var finalstring = "";
 var tags_one = document.getElementById("tags_one").getElementsByTagName("li");                
 var arrayLength = tags_one.length;
 for (var i = 0; i < arrayLength; i++) {
  if (i == "0") {
   finalstring = tags_one[i].id;    
  }
  else {
   finalstring = finalstring+','+tags_one[i].id;
  }
 }
 finalstring = finalstring+'-';
 
 var tags_two = document.getElementById("tags_two").getElementsByTagName("li");                
 arrayLength = tags_two.length;               
 for (i = 0; i < arrayLength; i++) {
  if (i == "0") {
   finalstring = finalstring+tags_two[i].id;    
  }
  else {
   finalstring = finalstring+','+tags_two[i].id;
  }
 }
 finalstring = finalstring+'-';               

 var tags_three = document.getElementById("tags_three").getElementsByTagName("li");                
 arrayLength = tags_three.length;               
 for (i = 0; i < arrayLength; i++) {
  if (i == "0") {
   finalstring = finalstring+tags_three[i].id;    
  }
  else {
   finalstring = finalstring+','+tags_three[i].id;
  }
 }
 
var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function() {
		if (this.readyState == 4 && this.status == 200) {
			if (this.responseText != 'OK') {
				$.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
			}
			if (this.responseText == 'OK') {
    $.growl.notice({ message: "Preferences saved", duration: "3000", size: "large", location: "tc" });
   }
  }
		if (this.status != 200 && this.status != 0 && this.readyState != 4){
			$.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
		}
	};
	xmlhttp.open("GET", "classes/ajax/ac_tagprios.php?stratprio=" + encodeURIComponent(finalstring)
	+ "&userid=" + encodeURIComponent(u)
	+ "&delimiter=" + encodeURIComponent(c), true);
	xmlhttp.send(); 
}


// Strategy Editor 2.0 - QuickFill Options to edit lines
function stredit_quickfill(u, c, type, lineid, customid, lang) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    if (this.responseText == 'NOK') {
      $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
    else {
     stredit_updateline(u, c, lineid, this.responseText, lang);
     $('.bt_step_quickfill_'+lineid).tooltipster('close');
     $('.bt_step_edit_'+lineid).tooltipster('close');
    }
    }
    if (this.status != 200 && this.status != 0 && this.readyState != 4){
    $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
  };
  xmlhttp.open("GET", "classes/ajax/stredit_quickfill.php?lineid=" + encodeURIComponent(lineid)
  + "&userid=" + encodeURIComponent(u)
  + "&type=" + encodeURIComponent(type)
  + "&customid=" + encodeURIComponent(customid)
  + "&delimiter=" + encodeURIComponent(c), true);
  xmlhttp.send();
}


// Strategy Editor 2.0 - QuickFill Options to edit lines
function bb_strsave(u, c, lineid, lang) {
 
 var turn = document.getElementById('stredit_editstep_'+lineid).value;
 var inst = document.getElementById('stredit_editinst_'+lineid).value;

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    if (this.responseText == 'NOK') {
      $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
    else {
     var strArray = this.responseText.split("-");
     var inputID = strArray[1];
     if (strArray[0] == "2") {
      $.growl.error({ message: "Syntax error - opening brackets missing: '['. Please re-check, formatting might be broken.", duration: "5000", size: "large", location: "tc" });
     }
     if (strArray[0] == "1") {
      $.growl.error({ message: "Syntax error - closing brackets missing: ']'. I tried to fix this for you, but the formatted might be broken.", duration: "5000", size: "large", location: "tc" });
     }
     stredit_updateline(u, c, lineid, inputID, lang);
     $('.bt_step_quickfill_'+lineid).tooltipster('close');
     $('.bt_step_edit_'+lineid).tooltipster('close');
    }
    }
    if (this.status != 200 && this.status != 0 && this.readyState != 4){
     $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
  };
  xmlhttp.open("GET", "classes/ajax/stredit_saveline.php?lineid=" + encodeURIComponent(lineid)
  + "&userid=" + encodeURIComponent(u)
  + "&turn=" + encodeURIComponent(turn)
  + "&inst=" + encodeURIComponent(inst)
  + "&delimiter=" + encodeURIComponent(c), true);
  xmlhttp.send();
}

				
function bt_initialize_tooltips(thisline) {
    $('.bt_step_quickfill_'+thisline).tooltipster({
						maxWidth: '576',
						interactive: 'true',
						arrow: false,
      trigger: 'custom',
      triggerOpen: {
          click: true
      },
      triggerClose: {
          click: true,
          mouseleave: true
      },
						functionPosition: function(instance, helper, position){
							position.coord.top -= 10;
							position.coord.left -= 210;
							return position;
						}, 
						theme: 'tooltipster-usertooltip',
						side: ['bottom']
    });
    $('.bt_step_edit_'+thisline).tooltipster({
						maxWidth: '800',
						interactive: 'true',
						arrow: false,
      trigger: 'click',
						functionPosition: function(instance, helper, position){
							position.coord.top -= 70;
							position.coord.left -= 344;
							return position;
						},     
						theme: 'tooltipster-bbedit',
						side: ['bottom']
    });
    $('.bt_step_delete_'+thisline).tooltipster({
						maxWidth: '400',
						interactive: 'true',
						arrow: false,
      trigger: 'custom',
      triggerOpen: {
          click: true
      },
      triggerClose: {
          click: true,
          mouseleave: true
      },
						functionPosition: function(instance, helper, position){
							position.coord.top -= 36;
						 position.coord.left -= -10;
							return position;
						},    
						theme: 'tooltipster-bbedit',
						side: ['bottom']
    });    
}	

// Strategy Editor 2.0 - Update Strategy Line
function stredit_updateline(u, c, lineid, prevline, l) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
     // alert(this.responseText);
    if (this.responseText == 'NOK') {
      $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
    else {
     $('#step_'+lineid).remove();
     $(this.responseText).insertAfter('#step_'+prevline);
     // $.growl.error({ message: "green", duration: "15000", size: "large", location: "tc" });
    }
    }
    if (this.status != 200 && this.status != 0 && this.readyState != 4){
    $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
  };
  xmlhttp.open("GET", "classes/ajax/stredit_updateline.php?lineid=" + encodeURIComponent(lineid)
  + "&userid=" + encodeURIComponent(u)
  + "&delimiter=" + encodeURIComponent(c)
  + "&lang=" + encodeURIComponent(l), true);
  xmlhttp.send();
}


// Strategy Editor 2.0 - Add Strategy Line
function stredit_addline(lineid, u, c, lang) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    if (this.responseText == 'NOK') {
      $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
    else {
     $(this.responseText).insertAfter('#step_'+lineid);
    }
    }
    if (this.status != 200 && this.status != 0 && this.readyState != 4){
    $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
  };
  xmlhttp.open("GET", "classes/ajax/stredit_addline.php?lineid=" + encodeURIComponent(lineid)
  + "&userid=" + encodeURIComponent(u)
  + "&language=" + encodeURIComponent(lang)
  + "&delimiter=" + encodeURIComponent(c), true);
  xmlhttp.send();
}

// Strategy Editor 2.0 - Delete Strategy Line
function stredit_removeline(lineid, u, c) {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    if (this.responseText == 'NOK') {
      $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
    if (this.responseText == 'lastlineNOK') {
     $.growl.error({ message: "You cannot delete all lines from a strategy. There has to be at least one line of instructions.", duration: "10000", size: "medium", location: "tr" });
    }
    if (this.responseText == 'OK') {
     $('.bt_step_delete_'+lineid).tooltipster('close');
     $('#step_'+lineid).remove();
    }
    }
    if (this.status != 200 && this.status != 0 && this.readyState != 4){
    $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
  };
  xmlhttp.open("GET", "classes/ajax/stredit_delline.php?lineid=" + encodeURIComponent(lineid)
  + "&userid=" + encodeURIComponent(u)
  + "&delimiter=" + encodeURIComponent(c), true);
  xmlhttp.send();
}

// Admin section - update stats on a pet
function adm_update_petstat(command, species) {
  var e = document.getElementById('adm_'+command+'_'+species);
  if (command != "npcid") {
   var val = e.options[e.selectedIndex].value;
  } 
  var newid = $('#new_npcid_'+species).val();

  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
    if (this.readyState == 4 && this.status == 200) {
    if (this.responseText == 'DUP') {
      $.growl.error({ message: "A pet with this ID already exists.", duration: "15000", size: "large", location: "tc" });
    }
    if (this.responseText == 'NOK') {
      $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
    if (this.responseText == 'OK') {
     $.growl.notice({ message: "Pet updated", duration: "5000", size: "medium", location: "bl" });
    }
    }
    if (this.status != 200 && this.status != 0 && this.readyState != 4){
    $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
  };
  xmlhttp.open("GET", "classes/ajax/adm_update_pet.php?species=" + encodeURIComponent(species)
  + "&command=" + encodeURIComponent(command)
  + "&newid=" + encodeURIComponent(newid)
  + "&value=" + encodeURIComponent(val), true);
  xmlhttp.send();
}


// Battle Table 2.0 - Save personal match record

function bt_record_fight(user,delim,stratid) {
 var p1_petid = '';
 var p2_petid = '';
 var p3_petid = '';
 var p1_substitute = '';
 var p2_substitute = '';
 var p3_substitute = '';
 var p1_subs = '';
 var p2_subs = '';
 var p3_subs = ''; 
 var p1_level = '';
 var p2_level = '';
 var p3_level = '';
 var p1_breed = '';
 var p2_breed = '';
 var p3_breed = '';
 
 p1_petid = parseInt(document.getElementById('rm_p1_s'+stratid+'_petid').innerHTML);
  if (p1_petid <= "20") {
   p1_level = parseInt(document.getElementById('bt_fb_counter_1').innerHTML); 
  }
  if (p1_petid > "20") {
   p1_subs = parseInt(document.getElementById('rm_p1_s'+stratid+'_subscount').innerHTML);
   if (p1_subs == "1") {
    p1_substitute = "0";
   }
   else {
    p1_substitute = parseInt(document.getElementById('bt_petcounter_1').innerHTML)-1;
   }
   p1_petid = document.getElementById('bt_fb_npcid_1_'+p1_substitute).innerHTML;
   p1_breed = $("input[name=bt_fb_br_1_"+p1_substitute+"]:checked").val();
  }
  
  p2_petid = parseInt(document.getElementById('rm_p2_s'+stratid+'_petid').innerHTML);
  if (p2_petid <= "20") {
   p2_level = parseInt(document.getElementById('bt_fb_counter_2').innerHTML); 
  }
  if (p2_petid > "20") {
   p2_subs = parseInt(document.getElementById('rm_p2_s'+stratid+'_subscount').innerHTML);
   if (p2_subs == "1") {
    p2_substitute = "0";
   }
   else {
    p2_substitute = parseInt(document.getElementById('bt_petcounter_2').innerHTML)-1;
   }
   p2_petid = document.getElementById('bt_fb_npcid_2_'+p2_substitute).innerHTML;
   p2_breed = $("input[name=bt_fb_br_2_"+p2_substitute+"]:checked").val();
  }
  
  p3_petid = parseInt(document.getElementById('rm_p3_s'+stratid+'_petid').innerHTML);
  if (p3_petid <= "20") {
   p3_level = parseInt(document.getElementById('bt_fb_counter_3').innerHTML); 
  }
  if (p3_petid > "20") {
   p3_subs = parseInt(document.getElementById('rm_p3_s'+stratid+'_subscount').innerHTML);
   if (p3_subs == "1") {
    p3_substitute = "0";
   }
   else {
    p3_substitute = parseInt(document.getElementById('bt_petcounter_3').innerHTML)-1;
   }
   p3_petid = document.getElementById('bt_fb_npcid_3_'+p3_substitute).innerHTML;
   p3_breed = $("input[name=bt_fb_br_3_"+p3_substitute+"]:checked").val();
  }
  
 var success = $("input[name=bt_fb_success]:checked").val();
 var attempts = parseInt(document.getElementById('bt_fb_counter_4').innerHTML);
 
 var xmlhttp = new XMLHttpRequest();
 xmlhttp.onreadystatechange = function() {
  if (this.readyState == 4 && this.status == 200) {
   if (this.responseText != 'OK') {
    $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
   }
   if (this.responseText == 'OK') {
    $('#bt_fb_saved').show(500);
    $('#bt_fb_form').hide(500);
   }
  }
  if (this.status != 200 && this.status != 0 && this.readyState != 4){
      $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
  }
 };
 xmlhttp.open("GET", "classes/ajax/bt_record_fight.php?strat=" + encodeURIComponent(stratid)
 + "&p1id=" + encodeURIComponent(p1_petid)
 + "&p1level=" + encodeURIComponent(p1_level)
 + "&p1breed=" + encodeURIComponent(p1_breed)
 + "&p1substitute=" + encodeURIComponent(p1_substitute)
 + "&p2id=" + encodeURIComponent(p2_petid)
 + "&p2level=" + encodeURIComponent(p2_level)
 + "&p2breed=" + encodeURIComponent(p2_breed)
 + "&p2substitute=" + encodeURIComponent(p2_substitute)
 + "&p3id=" + encodeURIComponent(p3_petid)
 + "&p3level=" + encodeURIComponent(p3_level)
 + "&p3breed=" + encodeURIComponent(p3_breed)
 + "&p3substitute=" + encodeURIComponent(p3_substitute)
 + "&success=" + encodeURIComponent(success)
 + "&attempts=" + encodeURIComponent(attempts)
 + "&userid=" + encodeURIComponent(user)
 + "&delimiter=" + encodeURIComponent(delim), true);
 xmlhttp.send(); 
}

// Battle Table 2.0 - Flipping through substitutes

function bt_petcardswap(pet,max,dir,stratid) { 
    var current = parseInt(document.getElementById('bt_petcounter_'+pet).innerHTML);
    var newcard = "";
    var change = "";
    // Borders:
    if (dir == "up") {
        if (current == max) {
            newcard = current;
            change = false;
        }
        else {
            newcard = current+1;
            change = true;
        }
    }
    if (dir == "down") {
        if (current == 1) {
            newcard = 1;
            change = false;
        }
        else {
            newcard = current-1;
            change = true;
        }
    }

    var currentact = current-1;
    var newcardact = newcard-1;
    if (change == true) {
        document.getElementById('bt_petcounter_'+pet).innerHTML = newcard;
        $('#petcard_'+pet+'_'+currentact).slideToggle(500);
        $('#petcard_'+pet+'_'+newcardact).slideToggle(500);
        $('#fb_card_'+pet+'_'+currentact).hide();
        $('#fb_card_'+pet+'_'+newcardact).show();
        if (newcard != "1") {
         $('#bt_subwarning_'+pet).css("display", "block");
        }
        else {
         $('#bt_subwarning_'+pet).css("display", "none");
        }
    }

   var urlchanger = '?Strategy='+stratid;
   
   var p1_activepet = '';
   var p2_activepet = '';
   var p3_activepet = '';
   var sublinkset = '';

    var rm_p1_special = document.getElementById('rm_p1_s'+stratid+'_special').innerHTML;
    if (rm_p1_special == "0") {
      p1_activepet = parseInt(document.getElementById('bt_petcounter_1').innerHTML)-1;
      if (p1_activepet != '0') {
       urlchanger = urlchanger+'&Substitutes=1:'+document.getElementById('rm_p1_s'+stratid+'_sub'+p1_activepet+'_species').innerHTML;
       sublinkset = '1';
      }
    }    

    var rm_p2_special = document.getElementById('rm_p2_s'+stratid+'_special').innerHTML;
    if (rm_p2_special == "0") {
      p2_activepet = parseInt(document.getElementById('bt_petcounter_2').innerHTML)-1;
      if (p2_activepet != '0') {
       if (sublinkset == '1') {
        urlchanger = urlchanger+'-2:'+document.getElementById('rm_p2_s'+stratid+'_sub'+p2_activepet+'_species').innerHTML;
       }
       if (sublinkset == '') {
        urlchanger = urlchanger+'&Substitutes=2:'+document.getElementById('rm_p2_s'+stratid+'_sub'+p2_activepet+'_species').innerHTML;
        sublinkset = '1';
       }
      }
    }

    var rm_p3_special = document.getElementById('rm_p3_s'+stratid+'_special').innerHTML;
    if (rm_p3_special == "0") {
      p3_activepet = parseInt(document.getElementById('bt_petcounter_3').innerHTML)-1;
      if (p3_activepet != '0') {
       if (sublinkset == '1') {
        urlchanger = urlchanger+'-3:'+document.getElementById('rm_p3_s'+stratid+'_sub'+p3_activepet+'_species').innerHTML;
       }
       if (sublinkset == '') {
        urlchanger = urlchanger+'&Substitutes=3:'+document.getElementById('rm_p3_s'+stratid+'_sub'+p3_activepet+'_species').innerHTML; 
       }
      }
    }
    window.history.replaceState("object or string", "Title", urlchanger);
}


// Set Cursor Position
 $.fn.setCursorPosition = function (position)
{
 this.each(function (index, elem) {
 if (elem.setSelectionRange) {
 elem.setSelectionRange(position, position);
 }
 else if (elem.createTextRange) {
 var range = elem.createTextRange();
 range.collapse(true);
 range.moveEnd('character', position);
 range.moveStart('character', position);
 range.select();
 }
 });
 return this;
 };

 // Get cursor position
 $.fn.getCursorPosition = function ()
{
 var el = $(this).get(0);
 var position = 0;
 if ('selectionStart' in el) {
 position = el.selectionStart;
 }
 else if ('selection' in document) {
 el.focus();
 var Sel = document.selection.createRange();
 var SelLength = document.selection.createRange().text.length;
 Sel.moveStart('character', -el.value.length);
 position = Sel.text.length - SelLength; }
 return position;
 };



function bb_stredit(lineid,type,customid) {
 var txtfield = document.getElementById('stredit_editinst_' + lineid);
 var selectedText;
 var startPos;
 var endPos;
 var newtext;

 // Grab selection if present
 // IE version
 if (document.selection != undefined) {
     txtfield.focus();
     var sel = document.selection.createRange();
     selectedText = sel.text;
 }
 // Mozilla version
 else if (txtfield.selectionStart != undefined) {
     startPos = txtfield.selectionStart;
     endPos = txtfield.selectionEnd;
     selectedText = txtfield.value.substring(startPos, endPos);
 }

 if (type == "simple") {
  var intaglt = customid.length + 2;
  var outaglt = customid.length + 3;
  var inittag = txtfield.value.slice(txtfield.selectionStart, txtfield.selectionEnd).substring(0,intaglt);
  var endtag = txtfield.value.slice(txtfield.selectionStart, txtfield.selectionEnd).substr(txtfield.value.slice(txtfield.selectionStart, txtfield.selectionEnd).length - outaglt);

  // selection is already wrapped in the same tags, remove tags!
  if (inittag == '[' + customid + ']' && endtag == '[/' + customid + ']') {
   startPos = txtfield.selectionStart;
   endPos = txtfield.selectionEnd -intaglt -outaglt;
   var midpiece = txtfield.value.slice(txtfield.selectionStart, txtfield.selectionEnd).slice(intaglt, -outaglt);
   newtext = txtfield.value.slice(0, txtfield.selectionStart) + midpiece + txtfield.value.slice(txtfield.selectionEnd, txtfield.value.length);
   txtfield.value = newtext;
   txtfield.setSelectionRange(startPos, endPos);
  }
  else {
   // No selection, append tags at cursor position
   if (selectedText == "") {
    var crspos = $("#stredit_editinst_" + lineid).getCursorPosition();
    newtext = txtfield.value.slice(0, crspos) + '[' + customid + ']' + '[/' + customid + ']' + txtfield.value.slice(txtfield.selectionEnd);
    txtfield.value = newtext;
    var newcrspos = crspos + intaglt;
    $("#stredit_editinst_" + lineid).setCursorPosition(newcrspos);
    txtfield.focus();
   }
   // Selection given, wrap with tags
   else {
    startPos = txtfield.selectionStart;
    endPos = txtfield.selectionEnd +intaglt +outaglt;
    newtext = txtfield.value.slice(0, txtfield.selectionStart) + '[' + customid + ']' + txtfield.value.slice(txtfield.selectionStart, txtfield.selectionEnd) + '[/' + customid + ']' + txtfield.value.slice(txtfield.selectionEnd);
    txtfield.value = newtext;
    txtfield.setSelectionRange(startPos, endPos);
   }
  }
  txtfield.focus();
 }
 
 if (type == "pet") {
     if (customid != "") {
         var intaglt = customid.length + 6;
         txtfield.selectionStart = txtfield.selectionEnd;
         var crspos = $("#stredit_editinst_" + lineid).getCursorPosition();
         newtext = txtfield.value.slice(0, crspos) + '[pet=' + customid + ']' + txtfield.value.slice(txtfield.selectionEnd);
         txtfield.value = newtext;
         var newcrspos = crspos + intaglt;
         $("#stredit_editinst_" + lineid).setCursorPosition(newcrspos);
         txtfield.focus();
     }
 }
 if (type == "enemy") {
     if (customid != "") {
         var intaglt = customid.length + 8;
         txtfield.selectionStart = txtfield.selectionEnd;
         var crspos = $("#stredit_editinst_" + lineid).getCursorPosition();
         newtext = txtfield.value.slice(0, crspos) + '[enemy=' + customid + ']' + txtfield.value.slice(txtfield.selectionEnd);
         txtfield.value = newtext;
         var newcrspos = crspos + intaglt;
         $("#stredit_editinst_" + lineid).setCursorPosition(newcrspos);
         txtfield.focus();
     }
 }
 if (type == "special") {
     if (customid != "") {
         var intaglt = customid.length;
         txtfield.selectionStart = txtfield.selectionEnd;
         var crspos = $("#stredit_editinst_" + lineid).getCursorPosition();
         newtext = txtfield.value.slice(0, crspos) + customid + txtfield.value.slice(txtfield.selectionEnd);
         txtfield.value = newtext;
         var newcrspos = crspos + intaglt;
         $("#stredit_editinst_" + lineid).setCursorPosition(newcrspos);
         txtfield.focus();
     }
 }

if (type == "spell") {
  if (customid != "") {
   var intaglt = customid.length + 11; //
   txtfield.selectionStart = txtfield.selectionEnd;
   var crspos = $("#stredit_editinst_" + lineid).getCursorPosition();
   newtext = txtfield.value.slice(0, crspos) + '[ability=' + customid + ']' + txtfield.value.slice(txtfield.selectionEnd); 
   txtfield.value = newtext;
   var newcrspos = crspos + intaglt;
   $("#stredit_editinst_" + lineid).setCursorPosition(newcrspos);
   txtfield.focus();
  }
 }  
}

// Expand News Article

function news_expand_article(id,curheight) {
 var hidden_height = $('#article_' + id).prop('scrollHeight');
 var real_height = $('#article_' + id).height();
 if (hidden_height > real_height) {
  $('#article_' + id).css('height',curheight);
  $('#article_' + id).css('max-height','100%');
  $('#article_' + id).animate({height:hidden_height}, 700);
  $('#article_expander_' + id).hide(500);
 }
}


function bb_articles(type,content,area) {
    var lng;
    if (area == 'article') {
     lng = document.getElementById('article_lng').innerHTML;
    }
    if (area == 'news') {
     lng = 'en_US';
    }
    if (area == '') {
     lng = 'en_US';
    }
    var txtfield = document.getElementById('article_ta_' + lng);
    var selectedText;
    var startPos;
    var endPos;
    var newtext;

    // Grab selection if present
    // IE version
    if (document.selection != undefined) {
        txtfield.focus();
        var sel = document.selection.createRange();
        selectedText = sel.text;
    }
    // Mozilla version
    else if (txtfield.selectionStart != undefined) {
        startPos = txtfield.selectionStart;
        endPos = txtfield.selectionEnd;
        selectedText = txtfield.value.substring(startPos, endPos);
    }
    
    if (type == "simple") {
        var intaglt = content.length + 2;
        var outaglt = content.length + 3;
        var inittag = txtfield.value.slice(txtfield.selectionStart, txtfield.selectionEnd).substring(0,intaglt);
        var endtag = txtfield.value.slice(txtfield.selectionStart, txtfield.selectionEnd).substr(txtfield.value.slice(txtfield.selectionStart, txtfield.selectionEnd).length - outaglt);

        // selection is already wrapped in the same tags, remove tags!
        if (inittag == '[' + content + ']' && endtag == '[/' + content + ']') {
            startPos = txtfield.selectionStart;
            endPos = txtfield.selectionEnd -intaglt -outaglt;
            var midpiece = txtfield.value.slice(txtfield.selectionStart, txtfield.selectionEnd).slice(intaglt, -outaglt);
            newtext = txtfield.value.slice(0, txtfield.selectionStart) + midpiece + txtfield.value.slice(txtfield.selectionEnd, txtfield.value.length);
            txtfield.value = newtext;
            txtfield.setSelectionRange(startPos, endPos);
        }
        else {
            // No selection, append tags at cursor position
            if (selectedText == "") {
                var crspos = $("#article_ta_" + lng).getCursorPosition();
                newtext = txtfield.value.slice(0, crspos) + '[' + content + ']' + '[/' + content + ']' + txtfield.value.slice(txtfield.selectionEnd);
                txtfield.value = newtext;
                var newcrspos = crspos + intaglt;
                $("#article_ta_" + lng).setCursorPosition(newcrspos);
                txtfield.focus();
            }
            // Selection given, wrap with tags
            else {
                startPos = txtfield.selectionStart;
                endPos = txtfield.selectionEnd +intaglt +outaglt;
                newtext = txtfield.value.slice(0, txtfield.selectionStart) + '[' + content + ']' + txtfield.value.slice(txtfield.selectionStart, txtfield.selectionEnd) + '[/' + content + ']' + txtfield.value.slice(txtfield.selectionEnd);
                txtfield.value = newtext;
                txtfield.setSelectionRange(startPos, endPos);
            }
        }
        txtfield.focus();
    }
    if (type == "single") {
        var intaglt = content.length + 2;
        txtfield.selectionStart = txtfield.selectionEnd;
        var crspos = $("#article_ta_" + lng).getCursorPosition();
        newtext = txtfield.value.slice(0, crspos) + '[' + content + ']' + txtfield.value.slice(txtfield.selectionEnd);
        txtfield.value = newtext;
        var newcrspos = crspos + intaglt;
        $("#article_ta_" + lng).setCursorPosition(newcrspos);
        txtfield.focus();
    }
    if (type == "url" && $('#bb_url_name').val() != "" && $('#bb_url').val() != "http://") {
        var intaglt = $('#bb_url_name').val().length + $('#bb_url').val().length + 12;
        txtfield.selectionStart = txtfield.selectionEnd;
        var crspos = $("#article_ta_" + lng).getCursorPosition();
        newtext = txtfield.value.slice(0, crspos) + '[url=' + $('#bb_url').val() + ']' + $('#bb_url_name').val() + '[/url]' + txtfield.value.slice(txtfield.selectionEnd);
        txtfield.value = newtext;
        var newcrspos = crspos + intaglt;
        $("#article_ta_" + lng).setCursorPosition(newcrspos);
        $('.add_url_tt').tooltipster('close');
        $('#bb_url_name').val('');
        $('#bb_url').val('');
        txtfield.focus();
    }
    if (type == "pet") {
        var e = document.getElementById("bb_pet_dd");
        var strUser = e.options[e.selectedIndex].value;
        if (strUser != "") {
            var intaglt = strUser.length + 6;
            txtfield.selectionStart = txtfield.selectionEnd;
            var crspos = $("#article_ta_" + lng).getCursorPosition();
            newtext = txtfield.value.slice(0, crspos) + '[pet=' + strUser + ']' + txtfield.value.slice(txtfield.selectionEnd);
            txtfield.value = newtext;
            var newcrspos = crspos + intaglt;
            $("#article_ta_" + lng).setCursorPosition(newcrspos);
            // $('.add_pet_tt').tooltipster('close');
            txtfield.focus();
        }
    }
    if (type == "skill") {
        var e = document.getElementById("bb_skill_dd");
        var strUser = e.options[e.selectedIndex].value;
        if (strUser != "") {
            var intaglt = strUser.length + 10;
            txtfield.selectionStart = txtfield.selectionEnd;
            var crspos = $("#article_ta_" + lng).getCursorPosition();
            newtext = txtfield.value.slice(0, crspos) + '[ability=' + strUser + ']' + txtfield.value.slice(txtfield.selectionEnd);
            txtfield.value = newtext;
            var newcrspos = crspos + intaglt;
            $("#article_ta_" + lng).setCursorPosition(newcrspos);
            // $('.add_skill_tt').tooltipster('close');
            txtfield.focus();
        }
    }
    if (type == "table") {
        var intaglt = 147;
        txtfield.selectionStart = txtfield.selectionEnd;
        var crspos = $("#article_ta_" + lng).getCursorPosition();
        newtext = txtfield.value.slice(0, crspos) + '[table]\n[tr]\n[td]\nTitle1[/td]\n[td]\nTitle2[/td]\n[/tr]\n[tr]\n[td]\nCell1[/td]\n[td]\nCell2[/td]\n[/tr]\n[tr]\n[td]\nCell3[/td]\n[td]\nCell4[/td]\n[/tr]\n[/table]' + txtfield.value.slice(txtfield.selectionEnd);
        txtfield.value = newtext;
        var newcrspos = crspos + intaglt;
        $("#article_ta_" + lng).setCursorPosition(newcrspos);
        txtfield.focus();
    }
    if (type == "username") {
        var e = document.getElementById("username");
        var strUser = e.options[e.selectedIndex].value;
        if (strUser != "") {
            var intaglt = strUser.length + 7;
            txtfield.selectionStart = txtfield.selectionEnd;
            var crspos = $("#article_ta_" + lng).getCursorPosition();
            newtext = txtfield.value.slice(0, crspos) + '[user=' + strUser + ']' + txtfield.value.slice(txtfield.selectionEnd);
            txtfield.value = newtext;
            var newcrspos = crspos + intaglt;
            $("#article_ta_" + lng).setCursorPosition(newcrspos);
            // $('.add_skill_tt').tooltipster('close');
            txtfield.focus();
        }
    }
    if (type == "img") {
       var imgalign = $("input[name=imgfloat]:checked").val();
       var addalign = "";
       if (imgalign == "1") {
         addalign = "-left";
       }
       if (imgalign == "2") {
         addalign = "-right";
       }
       if (imgalign == "3") {
         addalign = "-center";
       }
       if (imgalign == "4") {
         addalign = "-floatleft";
       }
       var intaglt = content.length + addalign.length + 6;
       txtfield.selectionStart = txtfield.selectionEnd;
       var crspos = $("#article_ta_" + lng).getCursorPosition();
       newtext = txtfield.value.slice(0, crspos) + '[img=' + content + addalign +']' + txtfield.value.slice(txtfield.selectionEnd);
       txtfield.value = newtext;
       var newcrspos = crspos + intaglt;
       $("#article_ta_" + lng).setCursorPosition(newcrspos);
       $('.add_img_tt').tooltipster('close');
       txtfield.focus();
    }    
}

// Articles - change displayed language input fields
function article_chlng(lng) {
    $(".articleedit_lng").removeClass('articleedit_lng_active');
    $("#atbt_"+lng).addClass('articleedit_lng_active');
    document.getElementById('article_lng').innerHTML = lng;
    document.getElementById('seleclang').value = lng;
    $(".language_input").css("display", "none");
    $("#article_" + lng).css("display", "block");
    var el = document.getElementById('article_ta_'+lng);
    var h = el.scrollHeight;
    el.style.height = h+"px";
}

// Articles - import English text into other languages
function art_import_en(lng,type) {
 if (type == "editor") {
    var entitle = document.getElementById('article_title_en_US').value;
    var encontent = document.getElementById('article_ta_en_US').value;
 }
 if (type == "translator") {
    var entitle = document.getElementById('en_title_translator').value;
    var encontent = document.getElementById('en_article_translator').value;
 }
 document.getElementById('article_title_'+lng).value = entitle;
 document.getElementById('article_ta_'+lng).value = encontent;
}

// Front page - Login with Battle.net loading page

function loadingbnetlogin() {
    $("div.indexloginform").hide();
    $("div.indexloadingscreen").show();
}

function hideloadingbnetlogin() {
    $("div.indexloginform").show();
    $("div.indexloadingscreen").hide();
}


// Tooltip initialization for Usernames

$(document).ready(function() {
    $('.alternatives_tt').tooltipster({
        content: 'Loading alternatives...',
        interactive: 'true',
        theme: 'tooltipster-usertooltip',
        updateAnimation: 'null',
        side: ['bottom'],
        trigger: ('ontouchstart' in window) ? 'click' : 'hover',
        trigger: 'custom',
        triggerOpen: {
        mouseenter: true,
        touchstart: true,
        click: true,
        tap: true
    },
    triggerClose: {
        mouseleave: true,
        originClick: true,
        click: true,
        tap: true,
        touchleave: true
    },
        arrow: false,
        animationDuration: [350,0],
        contentAsHTML: true,
        functionPosition: function(instance, helper, position){
            position.coord.top -= 170;
            position.coord.left -= 247;
            return position;
        },        
        functionReady: function() {
         $('.remodal-bg').addClass('addblur');        
        },
        functionAfter: function() {
         $('.remodal-bg').removeClass('addblur');        
        }, 
        functionBefore: function(instance, helper) {
            var $origin = $(helper.origin);
            var strat = $origin.attr('value');
            var user = $origin.attr('rel');
            $.get('classes/ajax/alternatives_tooltip.php?userid='+ user +'&strat=' + strat +'&l=' + current_language, function(data) {
             instance.content(data);
             $origin.data('loaded', true);
            });
        },
    });
});

$(document).ready(function() {
    $('.alternatives_tt2').tooltipster({
        trigger: 'custom',
        triggerOpen: {
            mouseenter: true,
            touchstart: true,
            click: true,
            tap: true
        },
        triggerClose: {
            mouseleave: true,
            originClick: true,
            click: true,
            tap: true,
            touchleave: true
        },

        arrow: false,
        theme: 'tooltipster-usertooltip',
        updateAnimation: null,
        functionReady: function() {
          $('.remodal-bg').addClass('addblur');
        },
        functionAfter: function() {
          $('.remodal-bg').removeClass('addblur');
        },

        side: ['right', 'bottom', 'left'],
functionPosition: function(instance, helper, pos) {
if (pos.side === 'right' || pos.side === 'left')
  pos.coord.top = helper.geo.origin.windowOffset.top;
  return pos;
},

        content: 'Loading alternatives...',
        contentAsHTML: true,
        interactive: true,
        functionBefore: function(instance, helper) {
            var $origin = $(helper.origin);
            if ($origin.data('loaded') === true) return;

            $.get ( '/classes/ajax/alternatives_tooltip.php' 
                  + '?userid='+ $origin.attr('rel') 
                  + '&strat=' + $origin.attr('value') 
                  + '&l=' + current_language
              , function(data) {
                instance.content(data);
                $origin.data('loaded', true);
            });
        },
    });
});


// Tooltip initialization for Usernames

function initialize_tooltips() {
    $('.username').tooltipster({
        content: 'Loading profile...',
        interactive: 'true',
        theme: 'tooltipster-usertooltip',
        updateAnimation: 'null',
        animationDuration: [350,0],
        contentAsHTML: true,
        functionBefore: function(instance, helper) {

            var $origin = $(helper.origin);
            var l = $origin.attr('value');
            var u = $origin.attr('rel');

            if ($origin.data('loaded') !== true) {
                $.get('classes/ajax/user_tooltip.php?userid='+ u +'&log=' + l +'&l=' + current_language, function(data) {
                    instance.content(data);
                    $origin.data('loaded', true);
                });
            }
        }
    });
}

$(document).ready(initialize_tooltips);

// Recalculate pet experience in battle tables

function bt_recalc_xp(base) {
    var mult = 1;
    var xp = 0;
    if (document.getElementById('xp_hat').checked) {
        mult = mult+0.1;
    }
    if (document.getElementById('xp_ltreat').checked) {
        mult = mult+0.25;
    }
    if (document.getElementById('xp_btreat').checked) {
        mult = mult+0.5;
    }
    if (document.getElementById('xp_petweek').checked) {
        mult = mult+2;
    }
    if (document.getElementById('xp_dmhat').checked) {
        mult = mult+0.1;
    }

    //xp = Math.round(base*mult*(1+9)*(25-1+5));

    var levelsteps = new Array(0, 0, 50, 160, 280, 475, 755, 1205, 1765, 2360, 3080, 3840, 4740, 5685, 6675, 7825, 9025, 10275, 11705, 13190, 14730, 16325, 18125, 19985, 21905, 23885);

    var rawxp = 0;
    var xpgaint = 0;
    var xpgain = 0;
    var calctarget = 0;
    var xpwaste = "";
    var newlevel = "";
    var trigger = "";
    var redxp = 0;
    var greenxp = 0;

    var i = 1;
    while (i < 25) {
        newlevel = "";
        if (trigger == 1) {
            xpwaste = 1;
            redxp = 1;
        }
        rawxp = Math.round(base*mult*(i+9)*(25-i+5));
        xpgaint = (levelsteps[i]+rawxp)-levelsteps[25];
        if (xpgaint < 0) {
            xpgain = rawxp;
        }
        else {
            xpgain = rawxp-xpgaint;
            trigger = 1;
            greenxp = 1;
        }
        if (xpwaste == 1) {
            trigger = 0;
        }
        calctarget = levelsteps[i]+rawxp;

        for (var o=0, len=levelsteps.length; o<len; o++) {
            if (calctarget >= levelsteps[o]) {
                newlevel = o;
            }
        }
        if (trigger == 1) {
            $('td.lvlrow_'+i).addClass('xpmax');
        }
        else {
            if (xpwaste == 1) {
                $('td.lvlrow_'+i).removeClass('xpmax');
                $('td.lvlrow_'+i).addClass('xpwaste');
            }
            else {
                $('td.lvlrow_'+i).removeClass('xpwaste');
                $('td.lvlrow_'+i).removeClass('xpmax');
            }
        }
        document.getElementById('xp_target_'+i).innerHTML = newlevel;
        document.getElementById('xp_level_'+i).innerHTML = xpgain;
        i++;
    }
    if (greenxp == 1) {
        document.getElementById("greenxp").style.display = "block";
    }
    else {
        document.getElementById("greenxp").style.display = "none";
    }
    if (redxp == 1) {
        document.getElementById("redxp").style.display = "block";
    }
    else {
        document.getElementById("redxp").style.display = "none";
    }
}


// Create Rematch string dynamically
function create_rematch(stratid, lang) {
 var rm_switch = document.getElementById('rm_steps_switch').innerHTML;
 var rm_name = document.getElementById('rm_name_'+stratid).innerHTML;
 var rm_fight = parseInt(document.getElementById('rm_fight_'+stratid).innerHTML);
 var rm_p1_string = '';
 var rm_p2_string = '';
 var rm_p3_string = '';
 var p1_breed = '';
 var p2_breed = '';
 var p3_breed = '';
 var p1_subcount = '';
 var p2_subcount = '';
 var p3_subcount = '';
 var p1_activepet = '';
 var p2_activepet = '';
 var p3_activepet = '';
 var preferences = '';
 var reqlevel = '';
 var rm_string = '';
 var reqhp = '';
 
 if (rm_fight == 0) {
     rm_fight = '';
 }
 else {
     rm_fight = rm_fight.toString(32).toUpperCase();
 }

 var rm_p1_special = document.getElementById('rm_p1_s'+stratid+'_special').innerHTML;

 if (rm_p1_special == "2") {
     rm_p1_string = document.getElementById('rm_p1_s'+stratid+'_qc').innerHTML;
     reqlevel = parseInt(document.getElementById('rm_p1_s'+stratid+'_level').innerHTML);
     reqhp = parseInt(document.getElementById('rm_p1_s'+stratid+'_petreqhp').innerHTML);
 }
 else if (rm_p1_special == "1") {
     rm_p1_string = document.getElementById('rm_p1_s'+stratid+'_qc').innerHTML;
 }
 else if (rm_p1_special == "0") {
   p1_subcount = parseInt(document.getElementById('rm_p1_s'+stratid+'_subscount').innerHTML);
   if (p1_subcount == "1") {
    p1_activepet = "0";
   }
   else {
    p1_activepet = parseInt(document.getElementById('bt_petcounter_1').innerHTML)-1;
   }
   p1_breed = document.getElementById('rm_p1_s'+stratid+'_sub'+p1_activepet+'_breed').innerHTML;
   if (p1_breed == '') {
     p1_breed = '0'; 
   }
   rm_p1_string = document.getElementById('rm_p1_s'+stratid+'_sub'+p1_activepet+'_skills').innerHTML+p1_breed+parseInt(document.getElementById('rm_p1_s'+stratid+'_sub'+p1_activepet+'_species').innerHTML).toString(32).toUpperCase();
 }    

 var rm_p2_special = document.getElementById('rm_p2_s'+stratid+'_special').innerHTML;

 if (rm_p2_special == "2") {
     rm_p2_string = document.getElementById('rm_p2_s'+stratid+'_qc').innerHTML;
     if (reqlevel < parseInt(document.getElementById('rm_p2_s'+stratid+'_level').innerHTML)) {
         reqlevel = parseInt(document.getElementById('rm_p2_s'+stratid+'_level').innerHTML);
     }
     if (reqhp < parseInt(document.getElementById('rm_p2_s'+stratid+'_petreqhp').innerHTML)) {
         reqhp = parseInt(document.getElementById('rm_p2_s'+stratid+'_petreqhp').innerHTML);
     }
 }
 else if (rm_p2_special == "1") {
   rm_p2_string = document.getElementById('rm_p2_s'+stratid+'_qc').innerHTML;
 }
 else if (rm_p2_special == "0") {
   p2_subcount = parseInt(document.getElementById('rm_p2_s'+stratid+'_subscount').innerHTML);
   if (p2_subcount == "1") {
    p2_activepet = "0";
   }
   else {
    p2_activepet = parseInt(document.getElementById('bt_petcounter_2').innerHTML)-1;
   }
   p2_breed = document.getElementById('rm_p2_s'+stratid+'_sub'+p2_activepet+'_breed').innerHTML;
   if (p2_breed == '') {
     p2_breed = '0'; 
   }
   rm_p2_string = document.getElementById('rm_p2_s'+stratid+'_sub'+p2_activepet+'_skills').innerHTML+p2_breed+parseInt(document.getElementById('rm_p2_s'+stratid+'_sub'+p2_activepet+'_species').innerHTML).toString(32).toUpperCase();
 }

 var rm_p3_special = document.getElementById('rm_p3_s'+stratid+'_special').innerHTML;

 if (rm_p3_special == "2") {
     rm_p3_string = document.getElementById('rm_p3_s'+stratid+'_qc').innerHTML;
     if (reqlevel < parseInt(document.getElementById('rm_p3_s'+stratid+'_level').innerHTML)) {
         reqlevel = parseInt(document.getElementById('rm_p3_s'+stratid+'_level').innerHTML);
     }
     if (reqhp < parseInt(document.getElementById('rm_p3_s'+stratid+'_petreqhp').innerHTML)) {
         reqhp = parseInt(document.getElementById('rm_p3_s'+stratid+'_petreqhp').innerHTML);
     }
 }
 if (rm_p3_special == "1") {
   rm_p3_string = document.getElementById('rm_p3_s'+stratid+'_qc').innerHTML;
 }
 else if (rm_p3_special == "0") {
   p3_subcount = parseInt(document.getElementById('rm_p3_s'+stratid+'_subscount').innerHTML);
   if (p3_subcount == "1") {
    p3_activepet = "0";
   }
   else {
    p3_activepet = parseInt(document.getElementById('bt_petcounter_3').innerHTML)-1;
   }
 
   p3_breed = document.getElementById('rm_p3_s'+stratid+'_sub'+p3_activepet+'_breed').innerHTML;
   if (p3_breed == '') {
     p3_breed = '0'; 
   }
   rm_p3_string = document.getElementById('rm_p3_s'+stratid+'_sub'+p3_activepet+'_skills').innerHTML+p3_breed+parseInt(document.getElementById('rm_p3_s'+stratid+'_sub'+p3_activepet+'_species').innerHTML).toString(32).toUpperCase();
 // alert(p3_activepet);
 // alert(document.getElementById('rm_p3_s'+stratid+'_sub'+p3_activepet+'_species').innerHTML);
 }    

 if (reqlevel > 1 || reqhp > 0) {
  preferences = 'P:'+reqhp+'::::'+reqlevel+'::';
 }
 else {
  preferences = '';
 }
  
 if (rm_switch == 1) {
  var xmlhttp = new XMLHttpRequest();

  xmlhttp.onreadystatechange = function() {
   if (this.readyState == 4 && this.status == 200) {
    var allsteps = this.responseText.replace(/xzzuvwzzx/g, "\\");
    rm_string = rm_name+':'+rm_fight+':'+rm_p1_string+':'+rm_p2_string+':'+rm_p3_string+':'+preferences+'N:'+allsteps;
    $('#rematch_string_'+stratid).attr('data-clipboard-text', rm_string);
   }
  };
  xmlhttp.open("GET", "classes/ajax/bt_rmsteps.php?stratid=" + encodeURIComponent(stratid) + "&lang=" + encodeURIComponent(lang), false);
  xmlhttp.send();
 }
 else {
  rm_string = rm_name+':'+rm_fight+':'+rm_p1_string+':'+rm_p2_string+':'+rm_p3_string+':'+preferences;
  $('#rematch_string_'+stratid).attr('data-clipboard-text', rm_string);
 }
}



// Adding or removing favourite strategy

function toggle_favstrat(sub,strat,u,c) {
    var xmlhttp = new XMLHttpRequest();
    var numfavs = parseInt(document.getElementById('favcounter').innerHTML);
    if (!numfavs) {
        numfavs = 0;
    }
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            if (this.responseText == 'FAV') {
                numfavs = numfavs+1;
                var upd = 1;
                document.getElementById('favstraticon').src = "images/icon_favstrat.gif"+"?a="+Math.random();
            }
            if (this.responseText == 'UNFAV') {
                if (numfavs > 1) {
                    numfavs = numfavs-1;
                }
                else {
                    numfavs = '';
                }
                var upd = 1;
                document.getElementById('favstraticon').src = "images/icon_unfavstrat.gif"+"?a="+Math.random();
            }
            if (upd == 1){
                document.getElementById('favcounter').innerHTML = numfavs;
            }
        }
        if (this.status != 200 && this.status != 0 && this.readyState != 4){
            $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
        }
    };
    xmlhttp.open("GET", "classes/ajax/strat_favs.php?sub=" + encodeURIComponent(sub)
    + "&strat=" + encodeURIComponent(strat)
    + "&userid=" + encodeURIComponent(u)
    + "&delimiter=" + encodeURIComponent(c), true);
    xmlhttp.send();
}


// Adding or removing favourite strategy

function rate_strategy(strat,u,c,r,o,avg) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            if (this.responseText != 'NOK' && this.responseText != '') {
                if (o == 'none') {
                    document.getElementById("rating_own_intro").style.display = "block";
                    document.getElementById("rating_own").style.display = "block";
                    document.getElementById('rating_total').innerHTML = '1';
                    var prerating = 0;
                }
                else {
                    var prerating = parseInt(document.getElementById('rating_own_number').innerHTML);
                }
                document.getElementById('rating_own_number').innerHTML = r;

                // Adjust average
                var newavg = parseFloat(this.responseText);
                document.getElementById('rating_average').innerHTML = newavg;
                if (newavg <= 1.25) {
                    $('#strat_stars').removeClass();
                    $('#strat_stars').addClass('strat_star_1');
                }
                if (newavg > 1.25) {
                    $('#strat_stars').removeClass();
                    $('#strat_stars').addClass('strat_star_1_5');
                }
                if (newavg > 1.75) {
                    $('#strat_stars').removeClass();
                    $('#strat_stars').addClass('strat_star_2');
                }
                if (newavg > 2.25) {
                    $('#strat_stars').removeClass();
                    $('#strat_stars').addClass('strat_star_2_5');
                }
                if (newavg > 2.75) {
                    $('#strat_stars').removeClass();
                    $('#strat_stars').addClass('strat_star_3');
                }
                if (newavg > 3.25) {
                    $('#strat_stars').removeClass();
                    $('#strat_stars').addClass('strat_star_3_5');
                }
                if (newavg > 3.75) {
                    $('#strat_stars').removeClass();
                    $('#strat_stars').addClass('strat_star_4');
                }
                if (newavg > 4.25) {
                    $('#strat_stars').removeClass();
                    $('#strat_stars').addClass('strat_star_4_5');
                }
                if (newavg > 4.75) {
                    $('#strat_stars').removeClass();
                    $('#strat_stars').addClass('strat_star_5');
                }
                document.getElementById("rating_suc").style.display = "block";
                document.getElementById('rating_suc').src = "images/bt_star_success.gif"+"?a="+Math.random();
            }
        }
        if (this.status != 200 && this.status != 0 && this.readyState != 4){
            $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
        }
    };
    xmlhttp.open("GET", "classes/ajax/strat_rate.php?strat=" + encodeURIComponent(strat)
    + "&rating=" + encodeURIComponent(r)
    + "&userid=" + encodeURIComponent(u)
    + "&delimiter=" + encodeURIComponent(c), true);
    xmlhttp.send();
}

// Tooltips for user profile preview warnings

function initialize_ttintrowarning() {
    $('.ttwarningintro').tooltipster({
        content: 'Add an intro text to activate this option',
        theme: 'tooltipster-smallnote',
        updateAnimation: 'null',
        animationDuration: 350,
    });
}

function initialize_ttsocmwarning() {
    $('.ttwarningsocm').tooltipster({
        content: 'Add the link to one of your social media profiles to activate this option',
        theme: 'tooltipster-smallnote',
        updateAnimation: 'null',
        animationDuration: 350,
    });
}

// Counter display

function count_remaining_profile(field){
    var maxlimit = 3000;
    var field_content = $(field).html();
    var countfield = parseInt(document.getElementById('intro_remaining').innerHTML);
        if (field_content.length > 2000) {
            document.getElementById('intro_remaining').innerHTML = field_content.length + "/3000";
        }
        else {
            document.getElementById('intro_remaining').innerHTML = "";
        }
}

// Functions for Messaging system

function msg_expand(f){
    $('#'+f).toggle(400);
}

function delete_msg(t,u,s,d){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == '') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                $('#thread_'+t).hide(1000);

                if (d == 'out') {
                    var outmsgs = parseInt(document.getElementById('outmsgs').innerHTML);
                    document.getElementById('outmsgs').innerHTML = outmsgs-1;
                    if (outmsgs == '1') {
                        $('#nomsgsboxout').show(1000);
                    }
                }
                if (d == 'in') {
                    var inmsgs = parseInt(document.getElementById('inmsgs').innerHTML);
                    document.getElementById('inmsgs').innerHTML = inmsgs-1;
                    if (inmsgs == '1') {
                        $('#nomsgsboxin').show(1000);
                    }
                    document.getElementById('seenmarker_'+t).innerHTML = 1;
                    var msgs = parseInt(document.getElementById('topmsgscount').innerHTML);
                    if (msgs == 1) {
                        document.getElementById('topmsgsin').innerHTML = '';
                        document.getElementById('topmsgscount').innerHTML = '';
                        document.getElementById('topmsgsout').innerHTML = '';
                        document.getElementById('pmsgsin').innerHTML = '';
                        document.getElementById('pmsgscount').innerHTML = '';
                        document.getElementById('pmsgsout').innerHTML = '';

                    }
                    if (msgs > 1) {
                        document.getElementById('topmsgscount').innerHTML = msgs-1;
                        document.getElementById('pmsgscount').innerHTML = msgs-1;
                    }
                }
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/ac_deletemsg.php?userid=" + encodeURIComponent(u) + "&delimiter=" + encodeURIComponent(s) + "&threadid=" + encodeURIComponent(t), true);
    xmlhttp.send();
}


function msg_markread(t,u,s,typ){
    var see = parseInt(document.getElementById('seenmarker_'+t).innerHTML);
    if (see == '0') {
        setTimeout(function(){
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200 && this.responseText == 'OK') {
                    if (typ == 1) {
                        document.getElementById('threadicon_'+t).src = "images/icon_sysmsgrd.png";
                    }
                    else {
                        document.getElementById('threadicon_'+t).src = "images/icon_msgrd.png";
                    }
                    document.getElementById('threadintro_'+t).style.fontWeight="normal";
                    document.getElementById('seenmarker_'+t).innerHTML = 1;
                    var msgs = parseInt(document.getElementById('topmsgscount').innerHTML);
                    if (msgs == 1) {
                        document.getElementById('topmsgsin').innerHTML = '';
                        document.getElementById('topmsgscount').innerHTML = '';
                        document.getElementById('topmsgsout').innerHTML = '';
                        document.getElementById('pmsgsin').innerHTML = '';
                        document.getElementById('pmsgscount').innerHTML = '';
                        document.getElementById('pmsgsout').innerHTML = '';
                    }
                    if (msgs > 1) {
                        document.getElementById('topmsgscount').innerHTML = msgs-1;
                        document.getElementById('pmsgscount').innerHTML = msgs-1;
                    }
                }
            };
            xmlhttp.open("GET", "classes/ajax/ac_markmsgread.php?userid=" + encodeURIComponent(u) + "&delimiter=" + encodeURIComponent(s) + "&threadid=" + encodeURIComponent(t), true);
            xmlhttp.send();
        }, 3000);
    }
}
// Blog - expand and contract comment window

function show_comments(h) {
    $("div.opencoms"+h).hide();
    $("div.closecoms"+h).show();
    $("div.entry"+h).show("slow");
    document.getElementById("entry"+h).style.display = "block";
}
function hide_comments(h) {
    $("div.opencoms"+h).show();
    $("div.closecoms"+h).hide();
    $("div.entry"+h).hide("slow");
    document.getElementById("entry"+h).style.display = "none";
}



// Ajax - save User profile intro

function save_intro(c,u,d) {
    var postText = document.getElementById('intro_field').value;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'EmptyOK') {
                if (c == 'profile') {
                    document.getElementById('intro_content').innerHTML = '';
                    document.getElementById('toggle_intro').src = "images/icon_eye_open.png";
                    $('#section_intro').addClass('profile_hidden');
                }
                if (c == 'tt') {
                    document.getElementById('ttintro').innerHTML = '';
                    document.getElementById("ttintro").style.display = 'none';
                    document.getElementById("ttintrot").checked = false;
                    document.getElementById("ttintrot").disabled = true;
                    document.getElementById("ttintrotr").style.opacity = "0.4";
                    document.getElementById("ttintrotr").style.filter = "alpha(opacity = 40)";
                    document.getElementById("ttintrotr2").style.opacity = "0.4";
                    document.getElementById("ttintrotr2").style.filter = "alpha(opacity = 40)";
                    if (document.getElementById("ttintroswitch").classList.contains('tooltipstered')) {
                        $('#ttintroswitch').tooltipster('content', 'Add an intro text to activate this option');
                    }
                    else {
                        initialize_ttintrowarning();
                    }
                }
            }
            else if (this.responseText == 'OK') {
                if (c == 'profile') {
                    var post = document.createElement('p');
                    post.textContent = postText;
                    post.innerHTML = post.innerHTML.replace('[u]', '<u>');
                    post.innerHTML = post.innerHTML.replace('[/u]', '</u>');
                    post.innerHTML = post.innerHTML.replace('[i]', '<i>');
                    post.innerHTML = post.innerHTML.replace('[/i]', '</i>');
                    post.innerHTML = post.innerHTML.replace('[b]', '<b>');
                    post.innerHTML = post.innerHTML.replace('[/b]', '</b>');;
                    post.innerHTML = post.innerHTML.replace(/\n/g, '<br>\n');
                    document.getElementById('intro_content').innerHTML = post.innerHTML;
                    document.getElementById('toggle_intro').src = "images/icon_eye_closed.png";
                    $('#section_intro').removeClass();
                }
                if (c == 'tt') {
                    var post = document.createElement('p');
                    post.textContent = postText;
                    post.innerHTML = post.innerHTML.replace('[u]', '<u>');
                    post.innerHTML = post.innerHTML.replace('[/u]', '</u>');
                    post.innerHTML = post.innerHTML.replace('[i]', '<i>');
                    post.innerHTML = post.innerHTML.replace('[/i]', '</i>');
                    post.innerHTML = post.innerHTML.replace('[b]', '<b>');
                    post.innerHTML = post.innerHTML.replace('[/b]', '</b>');;
                    post.innerHTML = post.innerHTML.replace(/\n/g, '<br>\n');
                    var maxLength = 130;
                    if (post.innerHTML.length > maxLength) {
                        post.innerHTML = post.innerHTML.substr(0, maxLength);
                        post.innerHTML = post.innerHTML.substr(0, Math.min(post.innerHTML.length, post.innerHTML.lastIndexOf(" ")));
                        var addlink = 'true';
                    }

                    var arr = post.innerHTML.split('<br>');
                    arr = arr.sort(function(a, b) {
                        return a.length-b.length;
                    });
                    arrz = arr.pop().split(' ');
                    arrz = arrz.sort(function(a, b) {
                        return a.length-b.length;
                    });
                    var longestString = arrz.pop().length;

                    if (longestString > "35") {
                        post.innerHTML = '<a class=\'ut_contact\' target=\'_blank\' href=\'index.php?user='+u+'\'>Read full introduction</a>';
                    }
                    else {
                        if (addlink == 'true') {
                            post.innerHTML = post.innerHTML + '... <a class=\'ut_contact\' target=\'_blank\' href=\'index.php?user='+u+'\'>read more</a>';
                        }
                    }



                    // alert(post);
                    document.getElementById('ttintro').innerHTML = post.innerHTML;
                    document.getElementById("ttintro").style.display = 'block';
                    document.getElementById("ttintrot").checked = true;
                    document.getElementById("ttintrot").disabled = false;
                    document.getElementById("ttintrotr").style.opacity = "1";
                    document.getElementById("ttintrotr").style.filter = "alpha(opacity = 100)";
                    document.getElementById("ttintrotr2").style.opacity = "1";
                    document.getElementById("ttintrotr2").style.filter = "alpha(opacity = 100)";
                    $('#ttintroswitch').tooltipster('content', null);
                }
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/ac_saveintro.php?content=" + encodeURIComponent(postText)
    + "&userid=" + encodeURIComponent(u)
    + "&cat=" + encodeURIComponent(c)
    + "&delimiter=" + encodeURIComponent(d), true);
    xmlhttp.send();
}

// Ajax - save social media info

function save_socm(c,u,d) {
    var fab = document.getElementById('soc_facebook').value;
    var twi = document.getElementById('soc_twitter').value;
    var ing = document.getElementById('soc_instagram').value;
    var ytu = document.getElementById('soc_youtube').value;
    var red = document.getElementById('soc_reddit').value;
    var twt = document.getElementById('soc_twitch').value;

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            if (this.responseText == 'Empty') {
                if (c == 'profile') {
                    document.getElementById('toggle_socm').src = "images/icon_eye_open.png";
                    $('#section_socm').addClass('profile_hidden');
                    $('#socm_facebook').removeClass();
                    $('#socm_twitter').removeClass();
                    $('#socm_isntagram').removeClass();
                    $('#socm_youtube').removeClass();
                    $('#socm_reddit').removeClass();
                    $('#socm_twitch').removeClass();
                }
                if (c == 'tt') {
                    document.getElementById("socmcont").style.display = 'none';
                    document.getElementById("ttsocmt").checked = false;
                    document.getElementById("ttsocmt").disabled = true;
                    document.getElementById("ttsocmtr").style.opacity = "0.4";
                    document.getElementById("ttsocmtr").style.filter = "alpha(opacity = 40)";
                    document.getElementById("ttsocmtr2").style.opacity = "0.4";
                    document.getElementById("ttsocmtr2").style.filter = "alpha(opacity = 40)";
                    if (document.getElementById("ttsocmswitch").classList.contains('tooltipstered')) {
                        $('#ttsocmswitch').tooltipster('content', 'Add the link to one of your social media profiles to activate this option');
                    }
                    else {
                        initialize_ttsocmwarning();
                    }
                }
            }

            if (this.responseText == 'OK') {

                if (c == 'profile') {
                    if (fab == '') {
                        $('#socm_facebook').removeClass();
                    }
                    else {
                        $('#socm_facebook').removeClass();
                        $('#socm_facebook').addClass('profile_sm_facebook');
                        document.getElementById("socm_facebook").href = fab;
                    }
                    if (twi == '') {
                        $('#socm_twitter').removeClass();
                    }
                    else {
                        $('#socm_twitter').removeClass();
                        $('#socm_twitter').addClass('profile_sm_twitter');
                        document.getElementById("socm_twitter").href = twi;
                    }
                    if (ing == '') {
                        $('#socm_isntagram').removeClass();
                    }
                    else {
                        $('#socm_isntagram').removeClass();
                        $('#socm_isntagram').addClass('profile_sm_instagram');
                        document.getElementById("socm_isntagram").href = ing;
                    }
                    if (ytu == '') {
                        $('#socm_youtube').removeClass();
                    }
                    else {
                        $('#socm_youtube').removeClass();
                        $('#socm_youtube').addClass('profile_sm_youtube');
                        document.getElementById("socm_youtube").href = ytu;
                    }
                    if (red == '') {
                        $('#socm_reddit').removeClass();
                    }
                    else {
                        $('#socm_reddit').removeClass();
                        $('#socm_reddit').addClass('profile_sm_reddit');
                        document.getElementById("socm_reddit").href = red;
                    }
                    if (twt == '') {
                        $('#socm_twitch').removeClass();
                    }
                    else {
                        $('#socm_twitch').removeClass();
                        $('#socm_twitch').addClass('profile_sm_twitch');
                        document.getElementById("socm_twitch").href = twt;
                    }
                    document.getElementById('toggle_socm').src = "images/icon_eye_closed.png";
                    $('#section_socm').removeClass();
                }
                if (c == 'tt') {

                    document.getElementById("socmcont").style.display = 'block';
                    document.getElementById("ttsocmt").checked = true;
                    document.getElementById("ttsocmt").disabled = false;
                    document.getElementById("ttsocmtr").style.opacity = "1";
                    document.getElementById("ttsocmtr").style.filter = "alpha(opacity = 100)";
                    document.getElementById("ttsocmtr2").style.opacity = "1";
                    document.getElementById("ttsocmtr2").style.filter = "alpha(opacity = 100)";

                    if (fab == '') {

                        $('#socm_facebook').removeClass();
                    }
                    else {
                        $('#socm_facebook').removeClass();
                        $('#socm_facebook').addClass('ut_sm_facebook');
                        document.getElementById("socm_facebook").href = fab;
                    }
                    if (twi == '') {
                        $('#socm_twitter').removeClass();
                    }
                    else {
                        $('#socm_twitter').removeClass();
                        $('#socm_twitter').addClass('ut_sm_twitter');
                        document.getElementById("socm_twitter").href = twi;
                    }
                    if (ing == '') {
                        $('#socm_isntagram').removeClass();
                    }
                    else {
                        $('#socm_isntagram').removeClass();
                        $('#socm_isntagram').addClass('ut_sm_instagram');
                        document.getElementById("socm_isntagram").href = ing;
                    }
                    if (ytu == '') {
                        $('#socm_youtube').removeClass();
                    }
                    else {
                        $('#socm_youtube').removeClass();
                        $('#socm_youtube').addClass('ut_sm_youtube');
                        document.getElementById("socm_youtube").href = ytu;
                    }
                    if (red == '') {
                        $('#socm_reddit').removeClass();
                    }
                    else {
                        $('#socm_reddit').removeClass();
                        $('#socm_reddit').addClass('ut_sm_reddit');
                        document.getElementById("socm_reddit").href = red;
                    }
                    if (twt == '') {
                        $('#socm_twitch').removeClass();
                    }
                    else {
                        $('#socm_twitch').removeClass();
                        $('#socm_twitch').addClass('ut_sm_twitch');
                        document.getElementById("socm_twitch").href = twt;
                    }
                    $('#ttsocmswitch').tooltipster('content', null);
                }
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/ac_savesocm.php?fab=" + encodeURIComponent(fab)
    + "&twi=" + encodeURIComponent(twi)
    + "&ing=" + encodeURIComponent(ing)
    + "&ytu=" + encodeURIComponent(ytu)
    + "&red=" + encodeURIComponent(red)
    + "&twt=" + encodeURIComponent(twt)
    + "&cat=" + encodeURIComponent(c)
    + "&userid=" + encodeURIComponent(u)
    + "&delimiter=" + encodeURIComponent(d), true);
    xmlhttp.send();
}


// Ajax - change background picture of quickview user tooltips

function change_bg(i,u,d){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                document.getElementById('ttbgpic').src = "images/userbgs/" + i + ".jpg";
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/ac_changettbg.php?userid=" + encodeURIComponent(u) + "&delimiter=" + encodeURIComponent(d) + "&newbg=" + encodeURIComponent(i), true);
    xmlhttp.send();
}

function change_tt_settings(i,u,d){
    var xmlhttp = new XMLHttpRequest();
    var chkBox = document.getElementById(i);
    if (chkBox.checked) {
        var a = "1";
    }
    else {
        var a = "0";
    }
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                if (i == 'ttqf' && a == '1') {
                    document.getElementById("ttqf1").style.display = 'block';
                    document.getElementById("ttqf2").style.display = 'block';
                    document.getElementById("ttqf3").style.display = 'block';
                }
                if (i == 'ttqf' && a == '0') {
                    document.getElementById("ttqf1").style.display = 'none';
                    document.getElementById("ttqf2").style.display = 'none';
                    document.getElementById("ttqf3").style.display = 'none';
                }
                if (i == 'ttonstats' && a == '1') {
                    document.getElementById("ttonstat").style.display = 'block';
                }
                if (i == 'ttonstats' && a == '0') {
                    document.getElementById("ttonstat").style.display = 'none';
                }
                if (i == 'ttintrot' && a == '1') {
                    document.getElementById("ttintro").style.display = 'block';
                }
                if (i == 'ttintrot' && a == '0') {
                    document.getElementById("ttintro").style.display = 'none';
                }
                if (i == 'ttsocmt' && a == '1') {
                    document.getElementById("socmcont").style.display = 'block';
                }
                if (i == 'ttsocmt' && a == '0') {
                    document.getElementById("socmcont").style.display = 'none';
                }
                if (i == 'ttcoll' && a == '1') {
                    document.getElementById("col1").style.display = 'block';
                    document.getElementById("col2").style.display = 'block';
                    document.getElementById("col3").style.left = '43';
                    document.getElementById("col4").style.border = '';
                    document.getElementById("col5").style.left = '150';
                    document.getElementById("ttonstat").style.left = '150';
                    document.getElementById("col7").style.height = '45';
                }
                if (i == 'ttcoll' && a == '0') {
                    document.getElementById("col1").style.display = 'none';
                    document.getElementById("col2").style.display = 'none';
                    document.getElementById("col3").style.left = '30';
                    document.getElementById("col4").style.border = '1px solid #509bb9';
                    document.getElementById("col5").style.left = '130';
                    document.getElementById("ttonstat").style.left = '130';
                    document.getElementById("col7").style.height = '30';
                }


            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/ac_changettsets.php?userid=" + encodeURIComponent(u) + "&action=" + encodeURIComponent(a) + "&delimiter=" + encodeURIComponent(d) + "&cat=" + encodeURIComponent(i), true);
    xmlhttp.send();
}

// Activate or deactivate beta settings
function change_beta_setting(u,d){
    var xmlhttp = new XMLHttpRequest();
    var chkBox = document.getElementById('setbeta');
    if (chkBox.checked) {
        var a = "1";
    }
    else {
        var a = "0";
    }
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                $.growl.notice({ message: "Beta settings updated.", duration: "3000", size: "medium", location: "tc" });
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/ac_changebeta.php?userid=" + encodeURIComponent(u) + "&action=" + encodeURIComponent(a) + "&delimiter=" + encodeURIComponent(d), true);
    xmlhttp.send();
}


// Activate or deactivate beta settings
function stratedit_publish(u,d,s){
    var xmlhttp = new XMLHttpRequest();
    var chkBox = document.getElementById('setbeta');
    if (chkBox.checked) {
        var a = "1";
    }
    else {
        var a = "0";
    }

    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                if (a == "1") {
                    $('#stratedit_publ').show();
                    $('#stratedit_unpubl').hide();
                    $.growl.notice({ message: "Strategy published for the world to see!", duration: "3000", size: "medium", location: "tr" });
                }
                if (a == "0") {
                    $('#stratedit_publ').hide();
                    $('#stratedit_unpubl').show();
                    $.growl.error({ message: "Strategy unpublished!<br>Only you can see it now.", duration: "3000", size: "medium", location: "tr" });
                }
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/strat_publish.php?userid=" + encodeURIComponent(u) + "&action=" + encodeURIComponent(a) + "&strat=" + encodeURIComponent(s) + "&delimiter=" + encodeURIComponent(d), true);
    xmlhttp.send();
}

// User Collection Functions:

function LevelStats() {
    $('#ButtonLevels').addClass('statisticsactive');
    $('#ButtonFamily').removeClass('statisticsactive');
    $('#ButtonQuality').removeClass('statisticsactive');
    $('div.chartLevels').show();
    $('div.chartFamiliesC').hide();
    $('div.chartQualityC').hide();
}
function FamilyStats() {
    $('#ButtonLevels').removeClass('statisticsactive');
    $('#ButtonFamily').addClass('statisticsactive');
    $('#ButtonQuality').removeClass('statisticsactive');
    $('div.chartLevels').hide();
    $('div.chartFamiliesC').show();
    $('div.chartQualityC').hide();
}
function QualityStats() {
    $('#ButtonLevels').removeClass('statisticsactive');
    $('#ButtonFamily').removeClass('statisticsactive');
    $('#ButtonQuality').addClass('statisticsactive');
    $('div.chartLevels').hide();
    $('div.chartFamiliesC').hide();
    $('div.chartQualityC').show();
}

// Profile toggle between pages

function profile_about(u) {
    $('#ButtonAbout').addClass('profileactive');
    $('#ButtonCollection').removeClass('profileactive');
    $('#ButtonStrategies').removeClass('profileactive');
    $('#about').show('200');
    $('#collection').hide('200');
    $('#strategies').hide('200');
    window.history.replaceState("object or string", "Title", "index.php?user="+u);
}

function profile_collection(u) {
    $('#ButtonAbout').removeClass('profileactive');
    $('#ButtonCollection').addClass('profileactive');
    $('#ButtonStrategies').removeClass('profileactive');
    $('#about').hide('200');
    $('#collection').show('200');
    $('#strategies').hide('200');
    window.history.replaceState("object or string", "Title", "index.php?user="+u+"&display=Collection");
}

function profile_strategies(u) {
    $('#ButtonAbout').removeClass('profileactive');
    $('#ButtonCollection').removeClass('profileactive');
    $('#ButtonStrategies').addClass('profileactive');
    $('#about').hide('200');
    $('#collection').hide('200');
    $('#strategies').show('200');
    window.history.replaceState("object or string", "Title", "index.php?user="+u+"&display=Strategies");
}

// Messages system toggle between pages

function messages_inbox() {
    $('#ButtonInbox').addClass('settingsactive');
    $('#ButtonSent').removeClass('settingsactive');
    $('#ButtonWrite').removeClass('settingsactive');
    $('#inbox').show();
    $('#sent').hide();
    $('#write').hide();
    window.history.replaceState("object or string", "Title", "index.php?page=messages");
}
function messages_sent() {
    $('#ButtonInbox').removeClass('settingsactive');
    $('#ButtonSent').addClass('settingsactive');
    $('#ButtonWrite').removeClass('settingsactive');
    $('#inbox').hide();
    $('#sent').show();
    $('#write').hide();
    window.history.replaceState("object or string", "Title", "index.php?page=sentmsgs");
}
function messages_write() {
    $('#ButtonInbox').removeClass('settingsactive');
    $('#ButtonSent').removeClass('settingsactive');
    $('#ButtonWrite').addClass('settingsactive');
    $('#inbox').hide();
    $('#sent').hide();
    $('#write').show();
    window.history.replaceState("object or string", "Title", "index.php?page=writemsg");
}



function switch_tamers_menu(m) {
 $('.tamers_menu').removeClass('settingsactive');
 $('#'+m).addClass('settingsactive');
 $('.tamers_content').hide();
 $('#'+m+'_content').show();
}

// Text field functions:
jQuery(document).ready(function($) {
    $('.to-top-btn').on('click', function(e) {
        $('body, html').stop().animate({scrollTop: 0}, 'slow', 'swing');
        e.preventDefault();
    });

    $(window).scroll(function() {
        if($(window).scrollTop() > 400){
            //show the button when scroll offset is greater than 400 pixels
            $('.to-top-btn').fadeIn('slow');
        }else{
            //hide the button if scroll offset is less than 400 pixels
            $('.to-top-btn').fadeOut('slow');
        }
    });
});
//auto expand textarea

function collect_parent_scrollTops (el) {
	const result = [];

	while (el && el.parentNode && el.parentNode instanceof Element) {
		if (el.parentNode.scrollTop) {
			result.push ({
				node: el.parentNode,
				scrollTop: el.parentNode.scrollTop,
			});
}
		el = el.parentNode;
}

	return result;
}
function auto_adjust_textarea_size (area) {
  const parent_scrollTops = collect_parent_scrollTops (area);
  const doc_scrollTop = document.documentElement && document.documentElement.scrollTop;

  area.style.height = '';
  area.style.height = area.scrollHeight + 'px';

  parent_scrollTops.forEach (el => { el.node.scrollTop = el.scrollTop; });

  if (doc_scrollTop) {
    document.documentElement.scrollTop = doc_scrollTop;
}
}

$(document).ready(function() {

    /*
    * Bind to capslockstate events and update display based on state
    */
    $(window).bind("capsOn", function(event) {
        if ($("#password:focus").length > 0) {
            $("#capsWarning").show();
        }
    });
    $(window).bind("capsOff capsUnknown", function(event) {
        $("#capsWarning").hide();
    });
    $("#password").bind("focusout", function(event) {
        $("#capsWarning").hide();
    });
    $("#password").bind("focusin", function(event) {
        if ($(window).capslockstate("state") === true) {
            $("#capsWarning").show();
        }
    });


    /*
    * Bind to capslockstate events and update display based on state
    */
    $(window).bind("capsOn", function(event) {
        if ($("#passwordz:focus").length > 0) {
            $("#capsWarningz").show();
        }
    });
    $(window).bind("capsOff capsUnknown", function(event) {
        $("#capsWarningz").hide();
    });
    $("#passwordz").bind("focusout", function(event) {
        $("#capsWarningz").hide();
    });
    $("#passwordz").bind("focusin", function(event) {
        if ($(window).capslockstate("state") === true) {
            $("#capsWarningz").show();
        }
    });


    /*
    * Bind to capslockstate events and update display based on state
    */
    $(window).bind("capsOn", function(event) {
        if ($("#passwordy:focus").length > 0) {
            $("#capsWarningz").show();
        }
    });
    $(window).bind("capsOff capsUnknown", function(event) {
        $("#capsWarningz").hide();
    });
    $("#passwordy").bind("focusout", function(event) {
        $("#capsWarningz").hide();
    });
    $("#passwordy").bind("focusin", function(event) {
        if ($(window).capslockstate("state") === true) {
            $("#capsWarningz").show();
        }
    });

    /*
    * Initialize the capslockstate plugin.
    * Monitoring is happening at the window level.
    */
    $(window).capslockstate();

});

// Part 2 - Comment System functions:

function show_respond_field(h,parent,l,u,cat,v,style){
   $('div.respondbutton_'+parent).hide();
        $.post('classes/ajax/com_show_respond_field.php', {
            'language': l,
            'userid': u,
            'category': cat,
            'sortingid': h,
            'parent': parent,
            'visitorid': v,
            'styleset': style
            }, function(data) {
            $('#respondfield_'+parent).html(data);
        });
    $('#respondfield_'+parent).show('');
}

function count_remaining(field,p,h,s,l){
    var maxlimit = 3000;
    var countfield = parseInt(document.getElementById('com_remaining_'+p+'_'+h+'_'+s+'_'+l).innerHTML);
    if ( field.value.length > maxlimit ) {
        field.value = field.value.substring( 0, maxlimit );
        return false;
    } else {
        if (field.value.length > 2000) {
            document.getElementById('com_remaining_'+p+'_'+h+'_'+s+'_'+l).innerHTML = field.value.length + "/3000";
        }
        else {
            document.getElementById('com_remaining_'+p+'_'+h+'_'+s+'_'+l).innerHTML = "";
        }
    }
}

function count_remaining_msgs(field,p,l){
    var maxlimit = parseInt(l);
    document.getElementById('rsp_remaining_'+p).innerHTML = field.value.length + "/" + maxlimit;
}

function delete_comment(h,t,s,n,u,v,c){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                $("#"+h).hide(1000);
                if (t == 'main') {
                    var offsetter = parseInt(document.getElementById('offset_'+s+'_'+n).innerHTML);
                    var allcoms = parseInt(document.getElementById('allcoms_'+s+'_'+n).innerHTML);
                    document.getElementById('offset_'+s+'_'+n).innerHTML = offsetter-1;
                    document.getElementById('allcoms_'+s+'_'+n).innerHTML = allcoms-1;
                    if (c == '1') {
                        document.getElementById('com_head_numcoms_'+s).innerHTML = allcoms-1;
                    }
                    if (n == 'native') {
                        document.getElementById('com_head_numcoms_nat').innerHTML = allcoms-1;
                    }
                    if (n == 'en') {
                        document.getElementById('com_head_numcoms_en').innerHTML = allcoms-1;
                        document.getElementById('coms_head_counter_en').innerHTML = allcoms-1;
                    }
                }
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/com_delete.php?userid=" + encodeURIComponent(u) + "&delimiter=" + encodeURIComponent(v) + "&commentid=" + encodeURIComponent(h), true);
    xmlhttp.send();
}

function delete_int_comment(i,u,s){
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                $("#int_comment_box_"+i).hide(1000);
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/com_int_delete.php?userid=" + encodeURIComponent(u) + "&delimiter=" + encodeURIComponent(s) + "&commentid=" + encodeURIComponent(i), true);
    xmlhttp.send();
}

function edit_comment(h,u,v){
    var postText = document.getElementById('editcommentfield_'+h).value;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                var post = document.createElement('p');
                post.textContent = postText+' (edited)';
                post.innerHTML = post.innerHTML.replace('[u]', '<u>');
                post.innerHTML = post.innerHTML.replace('[/u]', '</u>');
                post.innerHTML = post.innerHTML.replace('[i]', '<i>');
                post.innerHTML = post.innerHTML.replace('[/i]', '</i>');
                post.innerHTML = post.innerHTML.replace('[b]', '<b>');
                post.innerHTML = post.innerHTML.replace('[/b]', '</b>');;
                post.innerHTML = post.innerHTML.replace(/\n/g, '<br>\n');
                document.getElementById('comcontent_'+h).innerHTML = post.innerHTML;
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/com_edit.php?userid=" + encodeURIComponent(u) + "&delimiter=" + encodeURIComponent(v) + "&commentid=" + encodeURIComponent(h) + "&content=" + encodeURIComponent(postText), true);
    xmlhttp.send();
}

function report_comment(h,t,s,n,l,u,v,c){
    var ReportText = document.getElementById('report_comment_field_'+h).value;
    var inapp = $("#inappropriate_"+h).is(":checked");
    var spam = $("#spam_"+h).is(":checked");
    var other = $("#other_"+h).is(":checked");
    if (inapp == true){
        var ReportType = "inappropriate";
    }
    if (spam == true){
        var ReportType = "spam";
    }
    if (other == true){
        var ReportType = "other";
    }

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                var role = parseInt(document.getElementById('CM_'+h).getAttribute('data-value'));
                if (ReportType == 'spam' || ReportType == 'inappropriate') {
                    if ((role && role < 50) || !role){
                        $("#"+h).hide(1000);
                        com_vote('0',h,u,v,'vote_grey','vote_green','vote_gold','vote_red','50');
                        document.getElementById("report_com_" + h).style.display = 'none';
                        if (t == 'main') {

                            var offsetter = parseInt(document.getElementById('offset_'+s+'_'+n).innerHTML);
                            var allcoms = parseInt(document.getElementById('allcoms_'+s+'_'+n).innerHTML);
                            document.getElementById('offset_'+s+'_'+n).innerHTML = offsetter-1;
                            document.getElementById('allcoms_'+s+'_'+n).innerHTML = allcoms-1;
                            if (c == '1') {
                                document.getElementById('com_head_numcoms_'+s).innerHTML = allcoms-1;
                            }
                            if (n == 'native') {
                                document.getElementById('com_head_numcoms_nat').innerHTML = allcoms-1;
                            }
                            if (n == 'en') {
                                document.getElementById('com_head_numcoms_en').innerHTML = allcoms-1;
                                document.getElementById('coms_head_counter_en').innerHTML = allcoms-1;
                            }
                        }
                    }
                }
                if (ReportType == 'other') {
                    document.getElementById("cant_report_" + h).style.display = 'block';
                    document.getElementById("report_com_" + h).style.display = 'none';

                }
                $.growl.notice({ message: "Your report was received.<br>Thank you very much!", duration: "3000", size: "medium", location: "tc" });
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/com_report.php?category=" + encodeURIComponent('0')
    + "&sortingid=" + encodeURIComponent(h)
    + "&lang=" + encodeURIComponent(l)
    + "&userid=" + encodeURIComponent(u)
    + "&delimiter=" + encodeURIComponent(v)
    + "&type=" + encodeURIComponent(ReportType)
    + "&content=" + encodeURIComponent(ReportText), true);
    xmlhttp.send();
}

function com_vote(v,h,u,c,cga,cgr,cgo,cre,gol,s){
    gol = parseInt(gol);
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            var votes = parseInt(document.getElementById('vote_display_'+h).innerHTML);
            var arrows = '';
            switch(this.responseText) {
                case 'Up':
                    votes = votes+1;
                    arrows = 'up';
                    break;
                case 'Down':
                    votes = votes-1;
                    arrows = 'down';
                    break;
                case 'ChangeUp':
                    votes = votes+2;
                    arrows = 'up';
                    break;
                case 'ChangeDown':
                    votes = votes-2;
                    arrows = 'down';
                    break;
                case 'CancelUp':
                    votes = votes-1;
                    arrows = 'none';
                  break;
                case 'CancelDown':
                    votes = votes+1;
                    arrows = 'none';
                 break;
            }
            switch(arrows) {
                case 'up':
                    if (s == "bright") {
                        document.getElementById('upvpic_'+h).src = "https://www.wow-petguide.com/images/icon_vote_up_green_bright.png";
                        document.getElementById('downvpic_'+h).src = "https://www.wow-petguide.com/images/icon_vote_down_bright.png";
                    }
                    else {
                        document.getElementById('upvpic_'+h).src = "https://www.wow-petguide.com/images/icon_vote_up_green.png";
                        document.getElementById('downvpic_'+h).src = "https://www.wow-petguide.com/images/icon_vote_down_grey.png";
                    }
                    break;
                case 'down':
                    if (s == "bright") {
                        document.getElementById('upvpic_'+h).src = "https://www.wow-petguide.com/images/icon_vote_up_bright.png";
                        document.getElementById('downvpic_'+h).src = "https://www.wow-petguide.com/images/icon_vote_down_red.png";
                    }
                    else {
                        document.getElementById('upvpic_'+h).src = "https://www.wow-petguide.com/images/icon_vote_up_grey.png";
                        document.getElementById('downvpic_'+h).src = "https://www.wow-petguide.com/images/icon_vote_down_red.png";
                    }
                    break;
                case 'none':
                    document.getElementById('upvpic_'+h).src = "https://www.wow-petguide.com/images/icon_vote_up_grey.png";
                    document.getElementById('downvpic_'+h).src = "https://www.wow-petguide.com/images/icon_vote_down_grey.png";
                    break;
            }
            if (votes == 0) {
                $('#vote_display_c_'+h).removeClass();
                $('#vote_display_c_'+h).addClass(cga);
            }
            if (votes < 0) {
                $('#vote_display_c_'+h).removeClass();
                $('#vote_display_c_'+h).addClass(cre);
            }
            if (votes > 0) {
                $('#vote_display_c_'+h).removeClass();
                $('#vote_display_c_'+h).addClass(cgr);
            }
            if (votes >= gol) {
                $('#vote_display_c_'+h).removeClass();
                $('#vote_display_c_'+h).addClass(cgo);
            }
            document.getElementById('vote_display_'+h).innerHTML = votes;
        }
    };
    xmlhttp.open("GET", "classes/ajax/com_vote.php?sortingid=" + encodeURIComponent(h)
    + "&userid=" + encodeURIComponent(u)
    + "&delimiter=" + encodeURIComponent(c)
    + "&type=" + encodeURIComponent(v), true);
    xmlhttp.send();
}

function show_en_coms(){
    $('#coms_native').hide('');
    $('#coms_en').show('');
    $('#coms_en_button').hide();
    $('#coms_native_button').show();
    $('#coms_head_title_nat').hide();
    $('#coms_head_title_en').show();
    document.getElementById("comfilternatoren").value = "en";
}

function show_native_coms(){
    $('#coms_native').show('');
    $('#coms_en').hide('');
    $('#coms_en_button').show();
    $('#coms_native_button').hide();
    $('#coms_head_title_nat').show();
    $('#coms_head_title_en').hide();
    document.getElementById("comfilternatoren").value = "nat";
}

function input_typing(h,l,p,s,act,inact,err) {
    var submitbutton = document.getElementById("comsubmit_"+p+"_"+s+"_"+l);
    var errorbox = document.getElementById("comerror_"+p+"_"+s+"_"+l);
    var namefield = document.getElementById("username_"+p+"_"+s+"_"+l);
    submitbutton.disabled = true;
    submitbutton.value = 'Validating...';
    $('#comsubmit_'+p+'_'+s+'_'+l).removeClass(act);
    $('#comsubmit_'+p+'_'+s+'_'+l).addClass(inact);
    var re = /\?|'|"|<|>|\[|\]|\||\{|\}|\$|\\|\/|#| /;
    if (re.test(h.value)) {
        errorbox.innerHTML = '<p class="'+err+'">Not accepted: # < > [ ] | { } " \' / \ $ ? and space';
        errorbox.style.display = 'block';
        submitbutton.value = 'Error';
        namefield.style.borderBottom = '1px solid #b53333';
    }
    else {
        var remail = /(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/;
            if (remail.test(h.value)) {
                errorbox.innerHTML = '<p class="'+err+'">Please do not use an email address';
                errorbox.style.display = 'block';
                submitbutton.value = 'Error';
                namefield.style.borderBottom = '1px solid #b53333';
            }
            else {
                errorbox.style.display = 'none';
                submitbutton.value = 'Validating...';
                namefield.style.borderBottom = '1px dashed #8e8e8e';
            }
    }
}

function pr_save_title(userid,delimiter){
    var newtitle = document.getElementById("titleselect").options[document.getElementById("titleselect").selectedIndex].value;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {

                document.getElementById('title_suc').src = "images/icon_saved.gif"+"?a="+Math.random();
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/pr_save_title.php?userid=" + encodeURIComponent(userid)
    + "&delimiter=" + encodeURIComponent(delimiter)
    + "&new_title=" + encodeURIComponent(newtitle), true);
    xmlhttp.send();
}

function pr_save_pet(userid,delimiter){
    var newpet = document.getElementById("petselect").options[document.getElementById("petselect").selectedIndex].value;
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                document.getElementById('pet_suc').src = "images/icon_saved.gif"+"?a="+Math.random();
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/pr_save_pet.php?userid=" + encodeURIComponent(userid)
    + "&delimiter=" + encodeURIComponent(delimiter)
    + "&new_pet=" + encodeURIComponent(newpet), true);
    xmlhttp.send();
}

function pr_save_fields_ajax(field_type,field_content,maxl,userid,delimiter){
    var field_maxlength = parseInt(maxl);
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                document.getElementById(field_type+'_suc').src = "images/icon_saved.gif"+"?a="+Math.random();
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/pr_save_fields.php?userid=" + encodeURIComponent(userid)
    + "&delimiter=" + encodeURIComponent(delimiter)
    + "&field_type=" + encodeURIComponent(field_type)
    + "&field_content=" + encodeURIComponent(field_content), true);
    xmlhttp.send();
}

function pr_save_intro_ajax(field_content,maxl,userid,delimiter){
    var field_maxlength = parseInt(maxl);
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                document.getElementById('intro_suc').src = "images/icon_saved.gif"+"?a="+Math.random();
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/pr_save_intro.php?userid=" + encodeURIComponent(userid)
    + "&delimiter=" + encodeURIComponent(delimiter)
    + "&field_content=" + encodeURIComponent(field_content), true);
    xmlhttp.send();
}

function pr_colview(u,d){
    var xmlhttp = new XMLHttpRequest();
    var chkBox = document.getElementById('pr_col');
    if (chkBox.checked) {
        var a = "1";
    }
    else {
        var a = "0";
    }
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            if (this.responseText == 'NOK') {
                $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
            }
            else if (this.responseText == 'OK') {
                document.getElementById('collection_suc').src = "images/icon_saved.gif"+"?a="+Math.random();
            }
        }
    };
    xmlhttp.open("GET", "classes/ajax/pr_colview.php?userid=" + encodeURIComponent(u) + "&action=" + encodeURIComponent(a) + "&delimiter=" + encodeURIComponent(d), true);
    xmlhttp.send();
}

function us_rematchsteps(u,d){
 var chkBox = document.getElementById('us_rematchsteps');
 if (chkBox.checked) {
  var a = "1";
 }
 else {
  var a = "0";
 }
 
 if (u == "") {
  document.getElementById('rm_steps_switch').innerHTML = a;
 }
 else {
  var xmlhttp = new XMLHttpRequest();
  xmlhttp.onreadystatechange = function() {
   if (this.readyState == 4 && this.status == 200) {
    if (this.responseText == 'NOK') {
     $.growl.error({ message: "There was a problem processing your request. Please refresh the page and try again. If this error persists, please contact <a href=\"mailto:xufu@wow-petguide.com?subject=Problem with comment system\" class=\"whitelink\">Aranesh</a>", duration: "15000", size: "large", location: "tc" });
    }
    else if (this.responseText == 'OK') {
     document.getElementById('rm_steps_switch').innerHTML = a;
    }
   }
  };
  xmlhttp.open("GET", "classes/ajax/pr_rmsteps.php?userid=" + encodeURIComponent(u) + "&action=" + encodeURIComponent(a) + "&delimiter=" + encodeURIComponent(d), true);
  xmlhttp.send();
 }
}


function check_username_ajax(username,l,p,s,act,inact,err){
    var re = /\?|'|"|<|>|\[|\]|\||\{|\}|\$|\\|\/|#| /;
    var remail = /(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))/;
    var submitbutton = document.getElementById("comsubmit_"+p+"_"+s+"_"+l);
    var errorbox = document.getElementById("comerror_"+p+"_"+s+"_"+l);
    var namefield = document.getElementById("username_"+p+"_"+s+"_"+l);

    if (username.length == '1') {
        errorbox.innerHTML = '<p class="'+err+'">Name is too short';
        errorbox.style.display = 'block';
        namefield.style.borderBottom = '1px solid #b53333';
        submitbutton.value = 'Error';
        submitbutton.disabled = true;
    }
    if (username.length > '15') {
        errorbox.innerHTML = '<p class="'+err+'">Name is too long';
        errorbox.style.display = 'block';
        namefield.style.borderBottom = '1px solid #b53333';
        submitbutton.value = 'Error';
        submitbutton.disabled = true;
    }
    if (username.length > '1' && username.length < '16') {
        if (!re.test(username) && !remail.test(username)) {
            var xmlhttp = new XMLHttpRequest();
            xmlhttp.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    if (this.responseText == 'NOK') {
                        errorbox.innerHTML = '<p class="'+err+'">This name is already in use';
                        errorbox.style.display = 'block';
                        namefield.style.borderBottom = '1px solid #b53333';
                        submitbutton.value = 'Error';
                        submitbutton.disabled = true;
                    }
                    else if (this.responseText == 'OK') {
                        errorbox.style.display = 'none';
                        namefield.style.borderBottom = '1px dashed #8e8e8e';
                        submitbutton.disabled = false;
                        submitbutton.value = 'Submit';
                        $('#comsubmit_'+p+'_'+s+'_'+l).removeClass(inact);
                        $('#comsubmit_'+p+'_'+s+'_'+l).addClass(act);
                    }
                }
            };
            xmlhttp.open("GET", "classes/ajax/com_username-checker.php?q=" + encodeURIComponent(username), true);
            xmlhttp.send();
        }
    }
    if (username.length == '0') {
        errorbox.style.display = 'none';
        namefield.style.borderBottom = '1px solid #b53333';
        submitbutton.value = 'Name required';
        submitbutton.disabled = true;
    }
}

function add_offset(h,l,o){
    var offsetter = parseInt(document.getElementById('offset_'+h+'_'+l).innerHTML);
    offset = offsetter+parseInt(o);
    document.getElementById('offset_'+h+'_'+l).innerHTML = offset;
}

function load_more_coms(h,l,cat,s,style,u,v,vis,numco,lan,d,f){
    document.getElementById("addcomsb_" + h + "_" + l).style.display = 'none';
    var offsetter = parseInt(document.getElementById('offset_'+h+'_'+l).innerHTML);
    var numcoms = parseInt(document.getElementById('numcoms_'+h+'_'+l).innerHTML);
    var allcoms = parseInt(document.getElementById('allcoms_'+h+'_'+l).innerHTML);
    var remcoms = parseInt(document.getElementById('remcoms_'+h+'_'+l).innerHTML);
    var offset = offsetter+numcoms;

    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById('offset_'+h+'_'+l).innerHTML = offset;
            $("#com_table_"+h+"_"+l+" > tbody").append(this.responseText);
            initialize_tooltips();
            remcoms = remcoms-numcoms;
            document.getElementById('remcoms_'+h+'_'+l).innerHTML = remcoms;

            if (numcoms >= remcoms) {
                document.getElementById('addcomstext_'+h+'_'+l).innerHTML = 'Load last '+remcoms+' comment(s)';
            }

            if (offset+numcoms < allcoms) {
                document.getElementById("addcomsb_" + h +"_"+l).style.display = 'block';
            }
            var winh = 0;
            if( typeof( window.innerWidth ) == 'number' ) {
                //Non-IE
                winh = window.innerHeight;
            } else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
                //IE 6+ in 'standards compliant mode'
                winh = document.documentElement.clientHeight;
            } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
                //IE 4 compatible
                winh = document.body.clientHeight;
            }
            winh = winh-220;
            setTimeout(function(){$('body, html').animate({ scrollTop: '+='+winh+'px' }, 1000)}, 400);
        }

        if (this.status != 200 && this.status != 0 && this.readyState != 4){
            document.getElementById("errorfetch_" + h +"_"+l).style.display = 'block';
        }
    };
    xmlhttp.open("GET", "classes/ajax/com_morecoms.php?category=" + encodeURIComponent(cat)
    + "&sortingid=" + encodeURIComponent(s)
    + "&styleset=" + encodeURIComponent(style)
    + "&offset=" + encodeURIComponent(offset)
    + "&userid=" + encodeURIComponent(u)
    + "&delimiter=" + encodeURIComponent(v)
    + "&visitorid=" + encodeURIComponent(vis)
    + "&numcoms=" + encodeURIComponent(numco)
    + "&lang=" + encodeURIComponent(lan)
    + "&natoren=" + encodeURIComponent(l)
    + "&comfilter=" + encodeURIComponent(f)
    + "&editd=" + encodeURIComponent(d), true);
    xmlhttp.send();
}
