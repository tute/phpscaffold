var hiddenElements = new Array();

function selectAll(name) {
	var e = $(name);
	e.focus();
	e.select();
	try { // only works on ie
		var r = e.createTextRange();
		r.execCommand("Copy");
	}
	catch (e) {}
}

function showIt(name) {
	$(name).morph('height:400px; background: #eee; color: #CC0000;', { duration:0.3 } );
	hiddenElements.splice(hiddenElements.indexOf(name),1);
	$(name).style.overflow = 'auto';
}

function hideIt(name) {
	$(name).style.overflow = 'hidden';
	$(name).morph('height:10px; background: #fff; color: #DEB9A3;', { duration:0.3 } );
	hiddenElements[hiddenElements.length] = (name);
}

function toggle(name) {
	if ( hiddenElements.indexOf(name) == -1 ) {
		hideIt(name);
	} else {
		showIt(name);
	}
}

function showNew() {
	if ($('new_table').style.display != 'none') {
		showAll();
		Effect.toggle('new_table', 'blind', { duration:0.3 } );
	} else {
		hideAll();
		Effect.toggle('new_table', 'blind', { duration:0.3 } );
	}
}

function showAll() {
	var nodes = document.getElementsByClassName('textarea');
	for (var i = 0; i < nodes.length; i++) {
		showIt(nodes[i].id);
	}
}

function hideAll() {
	var nodes = document.getElementsByClassName('textarea');
	for (var i = 0; i < nodes.length; i++) {
		hideIt(nodes[i].id);
	}
}

function showHint(e) {
	Effect.toggle( e, 'blind', { duration:0.3 } );
}