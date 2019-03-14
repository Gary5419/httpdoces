$(function(){
	$("#btnNaviTheme, #dropdownmenu").hover(
		function(){
			$("ul#dropdownmenu li").show();
			$('iframe#frm').css({
				'left': $("#dropdownmenu").css('left'),
				'top': $("#dropdownmenu").css('top'),
				'width': $("#dropdownmenu").css('width'),
				'height': $("#dropdownmenu").css('height'),
				'display': 'block'
			});
		},
		function(){
			$("ul#dropdownmenu li").hide();
			$('iframe#frm').css('display','none');
		}
	);
});