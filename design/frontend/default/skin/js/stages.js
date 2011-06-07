$(document).ready(function() {

$('dl.ms dt a[title]').qtip({
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
});

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
	
    
$(".scrollable").scrollable();*/
});