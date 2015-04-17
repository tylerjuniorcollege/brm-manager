var brmTitle = new Bloodhound({
	datumTokenizer: Bloodhound.tokenizers.obj.whitespace('value'),
  	queryTokenizer: Bloodhound.tokenizers.whitespace,
  	remote: { 
  		url: '/search/brm/title?q=%QUERY'
  	}
});

$('#layoutSearch').typeahead({
	highlight: true,
	hint: true,
	minLength: 1
}, {
	name: 'brm-title',
	displayKey: 'value',
	source: brmTitle.ttAdapter(),
	templates: {
		header: '<h3 class="search-name">BRM Title Search</h3>',
		suggestion: Handlebars.compile('<p><a href="{{link}}">{{value}}</a></p>')
	}
});


$(function () { $("input,select,textarea").not("[type=submit]").jqBootstrapValidation(); } );