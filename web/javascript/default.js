PpkJS = {};

PpkJS.processReturn = function(data) {
	$.unblockUI();
	if (!data.success) {
		if (data.message != null) {
			PpkJS.showBlockMessage(data.message, true);
		} else {
			jQuery.each(data.content, function(key, value) {
				jQuery.each(value, function(k, v) {
					$("[id=" + k + "]").each(function() {
						var divError = $("<div/>");
						divError.attr("class", "form-error");
						var icon = $("<div/>");
						icon.attr("class", "form-error-icon");
						divError.prepend(icon);
						var message = $("<div/>");
						message.attr("class", "form-error-message");
						message.html(v);
						divError.append(message);
						var offset = ($(this).parent().parent().height() / 2);
						divError.css({
							'margin-top' : parseInt(offset) - 10
						});
						$(this).parent().parent().append(divError);
					});
				});
			});
		}
	} else {
		if (data.redirecturl != null) {
			PpkJS.showBlockMessage(data.message, false, function() {
				window.location.href = data.redirecturl
			});
		} else {
			PpkJS.showBlockMessage(data.message, false);
		}
	}
}

PpkJS.showBlockLoading = function() {
	$.blockUI({
		message : '<b>Processando</b>',
		css : {
			border : 'none',
			padding : '15px',
			backgroundColor : '#000',
			'-webkit-border-radius' : '10px',
			'-moz-border-radius' : '10px',
			'border-radius' : '10px',
			opacity : .4,
			color : '#fff',
			'font-size' : '16px'
		},
		overlayCSS : {
			opacity : 0.2
		},
		baseZ : 10009,
		fadeOut : 50,
		fadeIn : 50
	});
}

PpkJS.showBlockMessage = function(message, isErrorMessage, callback) {
	if (isErrorMessage == null)
		isErrorMessage = false;
	$.blockUI({
		message : message,
		css : {
			border : 'none',
			padding : '15px',
			backgroundColor : (isErrorMessage ? '#BF1717' : '#69a64d'),
			'-webkit-border-radius' : '10px',
			'-moz-border-radius' : '10px',
			'border-radius' : '10px',
			opacity : (isErrorMessage ? .4 : .7),
			color : '#FFF',
			'font-size' : '16px',
			cursor : 'pointer'
		},
		overlayCSS : {
			backgroundColor : (isErrorMessage ? '#FA3434' : '#5f82bb'),
			opacity : 0.2,
			cursor : 'pointer'
		},
		baseZ : 10009,
		fadeOut : 50,
		fadeIn : 50
	});
	if (callback != null) {
		setTimeout(callback, 1000);
	} else {
		$('.blockUI').attr('title', 'Click para continuar').click($.unblockUI);
	}
}

$(function() {
	$.datepicker.setDefaults({
		dayNames : [ 'Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira',
				'Quinta-feira', 'Sexta-feira', 'Sábado' ],
		dayNamesMin : [ 'D', 'S', 'T', 'Q', 'Q', 'S', 'S' ],
		monthNames : [ 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio',
				'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro',
				'Dezembro' ]
	})
});