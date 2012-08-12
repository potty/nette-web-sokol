/**
 * Equal Heights Plugin
 * Equalize the heights of elements. Great for columns or any elements
 * that need to be the same size (floats, etc).
 * 
 * Version 1.0
 * Updated 12/109/2008
 *
 * Copyright (c) 2008 Rob Glazebrook (cssnewbie.com) 
 *
 * Usage: $(object).equalHeights([minHeight], [maxHeight]);
 * 
 * Example 1: $(".cols").equalHeights(); Sets all columns to the same height.
 * Example 2: $(".cols").equalHeights(400); Sets all cols to at least 400px tall.
 * Example 3: $(".cols").equalHeights(100,300); Cols are at least 100 but no more
 * than 300 pixels tall. Elements with too much content will gain a scrollbar.
 * 
 */

//(function($) {
//    $.fn.equalHeights = function(minHeight, maxHeight) {
//        tallest = (minHeight) ? minHeight : 0;
//        this.each(function() {
//            if($(this).height() > tallest) {
//                tallest = $(this).height();
//            }
//        });
//        if((maxHeight) && tallest > maxHeight) tallest = maxHeight;
//        return this.each(function() {
//            $(this).height(tallest).css("overflow","auto");
//	    //$(this).height(tallest).css("overflow","hidden");
//        });
//    }
//})(jQuery);
//
//
//$(document).ready(function() {
//    $("#content, #sidebar").equalHeights();
//});

/* Czech initialisation for the jQuery UI date picker plugin. */
/* Written by Tomas Muller (tomas@tomas-muller.net). */
jQuery(function($) {
    $.datepicker.regional['cs'] = {
        closeText: 'Zavřít',
        prevText: '&#x3c;Dříve',
        nextText: 'Později&#x3e;',
        currentText: 'Nyní',
        monthNames: ['leden', 'únor', 'březen', 'duben', 'květen', 'červen', 'červenec', 'srpen',
            'září', 'říjen', 'listopad', 'prosinec'],
        monthNamesShort: ['led', 'úno', 'bře', 'dub', 'kvě', 'čer', 'čvc', 'srp', 'zář', 'říj', 'lis', 'pro'],
        dayNames: ['neděle', 'pondělí', 'úterý', 'středa', 'čtvrtek', 'pátek', 'sobota'],
        dayNamesShort: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
        dayNamesMin: ['ne', 'po', 'út', 'st', 'čt', 'pá', 'so'],
        weekHeader: 'Týd',
        dateFormat: 'dd. mm. yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: '',
	changeYear: true
    };
    $.datepicker.setDefaults($.datepicker.regional['cs']);
});

$(function() {
    $.timepicker.regional['cs'] = {
	    timeOnlyTitle: 'Vyberte čas',
	    timeText: 'Čas',
	    hourText: 'Hodiny',
	    minuteText: 'Minuty',
	    secondText: 'Vteřiny',
	    currentText: 'Nyní',
	    closeText: 'Zavřít',
	    timeFormat: 'h:m',
	    ampm: false,
	    changeYear: true
    };
    $.timepicker.setDefaults($.timepicker.regional['cs']);

    $('input[data-dateinput-type]').dateinput({
	    datetime: {
		    dateFormat: 'd.m.yy',
		    timeFormat: 'h:mm'
	    },
	    'datetime-local': {
		    dateFormat: 'd.m.yy',
		    timeFormat: 'h:mm'
	    },
	    date: {
		    dateFormat: 'd.m.yy'
	    },
	    month: {
		    dateFormat: 'MM yy'
	    },
	    week: {
		    dateFormat: "w. 'týden' yy"
	    },
	    time: {
		    timeFormat: 'h:mm'
	    }
    });
});



/**
 * DataTable
 */
$(document).ready(function() {
    $('#data-table').dataTable({
	'bPaginate': false,
	'bInfo': false,
	'bFilter': false
	//'bJQueryUI': true
    });
} );


/**
 * Custom scripts
 */
$(function() {
   
   // Showing/hiding content
   $('.toggle').click(function() {
       $('.toggable').toggle();
   });
   
   // Adds red star after label of required form field
   $('label.required').append('<span class="red"> *</span>');
   
   // ColorBox init
   // TODO: Hazi EXCEPTIONS, pokud na strance neni includovany colorbox.js
   $('a.colorbox').colorbox();
   
});



/**
 * Select submit on change 
 */
$(function () {
	$(".onChangeSubmit").change(function () {
		$(this).closest("form").submit();
        });
});
