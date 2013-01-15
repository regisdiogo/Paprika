<?php
namespace helper;
if (!defined('PAPRIKA_PATH')) {
	die('Direct access not allowed');
}

class JavascriptHelper {

	const BASE_JAVASCRIPT_CALLBACK = 'javascript:(function(){ %1$s })();';	
	const LOCATION_HREF_TO = 'window.location.href = \'%1$s\';';
	const SHOW_LOADING = 'PpkJS.showBlockLoading();';	
	const ALERT_CONFIRM = 'if (confirm(\'%1$s\')) { return true; } else { return false; }';
	const PREVENT_CUT_COPY_PASTE = '$(\'#%1$s\').bind(\'cut copy paste\', function(e) { e.preventDefault(); });';
	const DATE_PICKER = '$(\'#%1$s\').datepicker(%2$s);';
	const DATE_PICKER_FORMAT = '{ dateFormat: \'%1$s\' }';
	const MASK = '$(\'#%1$s\').setMask({ mask: \'%2$s\' %3$s });';
	const CLICK_BIND = '$("#%1$s").click(function() { %2$s });';
	const AJAX_CALLBACK = ' $(\'.form-error\').remove(); PpkJS.processReturn(data);';
	
	const EVENT_CLICK_POST_FORM = '
$("#%1$s input").bind({ 
	"keyup" : function(event) { 
		if (event.keyCode == 13) { 
			$("#%2$s").click(); 
		} 
	} 
});';
		
	const AJAX_POST = '
%3$s
$.ajax({ 
	type : "POST", 
	url : $("#%1$s").attr("action"), 
	data : $("#%1$s").serialize(), 
	success : function(data) { 
		%2$s 
	}
});';
	
	const DIALOG_MODAL_WITH_URL = '
var out = $("<div id=\'.uimodal-output\'></div>"); 
$("body").append(out);
$.ajax({
	url: $(this).attr("href"),
	cache: false,
	dataType: "html",
	success: function (data) {
		out.html(data).dialog({ 
			modal : true, 
			title: "%1$s", 
			width : %2$s, 
			height : %3$s
		});
	}
}).done(function() {
	 %4$s 
});
return false;';
	
	const IMAGE_FANCYBOX = '
$("a[rel=%1$s]").fancybox({
	"titlePosition" : "over",
	"titleFormat" : function(title, currentArray, currentIndex, currentOpts) {
		return "<span id=\"fancybox-title-over\">%2$s " + (currentIndex + 1)	+ " de " + currentArray.length + (title.length ? " - " + title : "") + "</span>";
	}
});';

	public static function createAlertConfirm($message) {
		$jsFunction = sprintf(self::ALERT_CONFIRM, $message);
		return $jsFunction;
	}

	public static function createLocationHrefChange($linkTo) {
		$jsFunction = sprintf(self::LOCATION_HREF_TO, $linkTo);
		return self::surroundWithBaseCallback($jsFunction);
	}

	public static function createPreventCutCopyPaste($element) {
		return sprintf(self::PREVENT_CUT_COPY_PASTE, $element);
	}

	public static function createDatePicker($element, $format = null) {
		if (!$format) {
			return sprintf(self::DATE_PICKER, $element, '');
		}
		return sprintf(self::DATE_PICKER, $element, sprintf(self::DATE_PICKER_FORMAT, $format));
	}

	public static function createMask($elementId, $mask, $isDecimal = false) {
		if ($isDecimal) {
			return sprintf(self::MASK, $elementId, $mask, ', type:\'reverse\'');
		} else {
			return sprintf(self::MASK, $elementId, $mask, '');
		}
	}

	public static function bindAjaxPostInClick($buttonId, $formId) {
		$ajaxPost = self::createAjaxPost($formId);
		return sprintf(self::CLICK_BIND, $buttonId, $ajaxPost);
	}

	public static function createAjaxPost($formId) {
		$ajaxPost = sprintf(self::AJAX_POST, $formId, self::AJAX_CALLBACK, self::SHOW_LOADING);
		return $ajaxPost;
	}

	public static function createEventClickPostForm($formId, $submitId) {
		return sprintf(self::EVENT_CLICK_POST_FORM, $formId, $submitId);
	}

	public static function bindOpenModalFromURL($elementId, $title, $modalWidth, $modalHeight, $callback = '') {
		$modal = sprintf(self::DIALOG_MODAL_WITH_URL, $title, $modalWidth, $modalHeight, $callback);
		return sprintf(self::CLICK_BIND, $elementId, $modal);
	}
	
	public static function showFancyBox($objectsReference, $objectName) {
		return sprintf(self::IMAGE_FANCYBOX, $objectsReference, $objectName);
	}
	
	public static function bindClickById($objectId, $function) {
		return sprintf(self::CLICK_BIND, $objectId, $function);
	}

	private static function surroundWithBaseCallback($jsFunction) {
		return sprintf(self::BASE_JAVASCRIPT_CALLBACK, $jsFunction);
	}
}