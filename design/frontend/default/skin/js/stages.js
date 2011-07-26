$(document).ready(function() {
// Shake homepage numbers on hover
	//$('.s1').hover(function(){$('#s1').effect('shake', { times:3,distance:2 }, 100);},{/*do nothing*/});
	//$('.s2').hover(function(){$('#s2').effect('shake', { times:3,distance:2 }, 100);},{/*do nothing*/});
	
	$('#top a').click(function() {
		$('nav a').removeClass('active');
		$('#top a').addClass('active');
		$('html, body').animate({scrollTop: $(".os").offset().top},1000);
		$('html').removeClass();
		return false;
	});
	$('#one a, .os .lm, .gallery .g1 a').click(function() {
		$('nav a').removeClass('active');
		$('#one a').addClass('active');
		$('html, body').animate({scrollTop: $(".vis").offset().top},1000);
		$('html').removeClass();
		return false;
	});
	$('#two a, .vis .lm, .gallery .g2 a').click(function() {
		$('nav a').removeClass('active');
		$('#two a').addClass('active');
		$('html, body').animate({scrollTop: $(".av").offset().top},1000);
		$('html').removeClass();
		return false;
	});
	$('#three a, .av .lm').click(function() {
		$('nav a').removeClass('active');
		$('#three a').addClass('active');
		$('html, body').animate({scrollTop: $(".ss").offset().top},1000);
		$('html').removeClass();
		return false;
	});
	$('#four a, .ss .lm, .gallery .g3 a').click(function() {
		$('nav a').removeClass('active');
		$('#four a').addClass('active');
		$('html, body').animate({scrollTop: $(".ov").offset().top},1000);
		$('html').addClass('scroll');
		return false;
	});
	$('#five a, .ov .lm').click(function() {
		$('nav a').removeClass('active');
		$('#five a').addClass('active');
		$('html, body').animate({scrollTop: $(".git").offset().top},1000);
		$('html').removeClass();
		return false;
	});
	
	/*$('ul.gallery a').fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'elastic',
		'speedIn'		:	600, 
		'speedOut'		:	200, 
		'overlayShow'	:	false
	});*/
	
	/* Homepage waypoints
	$('.os .lm').waypoint(function(){$('nav a').removeClass('active');});
	
	$('.vis').waypoint(function(){
		$('nav a').removeClass('active');
		$('#one a').addClass('active');
	});
	$('.av').waypoint(function(){
		$('nav a').removeClass('active');
		$('#two a').addClass('active');
	});
	$('.ss').waypoint(function(){
		$('nav a').removeClass('active');
		$('#three a').addClass('active');
	});
	$('.ov').waypoint(function(){
		$('nav a').removeClass('active');
		$('#four a').addClass('active');
	}); */

/*$('dl.ms dt a[title]').qtip({
	content: {
		text: false // Use each elements title attribute
	},
	style: { 
		name: 'light',
		background: '#888',
		padding: 1,
		textAlign: 'center',
		color: '#fff',
		border: {
			width: 1,
			radius: 3,
			color: '#888'
		},
		tip: 'topLeft' // Notice the corner value is identical to the previously mentioned positioning corners
	},
	show: {
		delay:0
	},
	position: {
		adjust: {
			x:-10
		}
	}
});*/

/*$('.mrkt dt').qtip({
	content: {
		url: 'http://stages.ip4/design/frontend/default/template/stages/project/milestone_tooltip_form.html'
	},
	style: {
		width: 280,
		name: 'light',
		background: '#888',
		padding: 1,
		textAlign: 'center',
		color: '#fff',
		border: {
			width: 1,
			radius: 3,
			color: '#888'
		},
		tip: 'bottomLeft' // Notice the corner value is identical to the previously mentioned positioning corners
	},
	show: {
		delay:0,
		when: { event: 'click' }
	},
	hide: { when: { event: 'click' } },
	position: {
		adjust: {
			y:-80,
			x:-10
		}
	}
});

$('.dev dt').qtip({
	content: {
		url: 'http://stages.ip4/design/frontend/default/template/stages/project/milestone_tooltip_form.html'
	},
	style: {
		width: 280,
		name: 'light',
		background: '#888',
		padding: 1,
		textAlign: 'center',
		color: '#fff',
		border: {
			width: 1,
			radius: 3,
			color: '#888'
		},
		tip: 'topLeft' // Notice the corner value is identical to the previously mentioned positioning corners
	},
	show: {
		delay:0,
		when: { event: 'click' }
	},
	hide: { when: { event: 'click' } },
	position: {
		adjust: {
			y:2,
			x:-10
		}
	}
});


$('#qdate').datepicker();
	    
$(".scrollable").scrollable();

// for the homepage
$('#features .scrollable').scrollable({ 
		size:1,
		autoplay:true,
		loop:true,
		speed:1000,
		vertical:true,
		clickable:true,
		keyboard:true
	}).navigator("#footer ul").circular().autoscroll({interval:10000});
	*/
});