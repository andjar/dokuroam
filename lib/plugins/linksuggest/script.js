/* DOKUWIKI:include_once vendor/jquery.textcomplete.min.js */
function linksuggest_escape(text){
    return jQuery('<div/>').text(text).html();
}
jQuery(function(){
	$editor = jQuery('#wiki__text');
	$editor.textcomplete([ 
    { //page search
    	appendTo: 'body',
        match: /\[{2}([\w\-\.:]*)$/,
        maxCount:50, 
        search: function (term, callback) {
        	if($editor.data('linksuggest_off') == 1){
        		callback([]);return;
        	}
        	jQuery.post( 
                DOKU_BASE + 'lib/exe/ajax.php',
                {call:'plugin_linksuggest',
                    q:term,
                    ns:JSINFO['namespace'],
                    id:JSINFO['id'],
                },
                function (data) {
                    data=JSON.parse(data);
                    callback(jQuery.map(data.data,function(item){
                        var id = item.id;
                        
                        if(item.type === 'd')
                            id = id + ':';
                        
                        return {id:id,
                            ns:item.ns,
                            title:item.title,
                            type:item.type,
                            rootns:item.rootns
                            };
                    }));
                }
            );
        },
        template:function(item){ //dropdown list
            var image = '';
            var title = item.title?' ('+linksuggest_escape(item.title)+')':'';
            var value = item.id;
            
            if(item.rootns){ //page is in root namespace
                value = ':'+value;
            }
            if(item.type === 'd'){ //namespace
                image = 'ns.png';
            } else { //file
                image = 'page.png';
            }
            return '<img src="'+DOKU_BASE+'lib/images/'+image+'"> '+linksuggest_escape(value) + title;
        },
        index: 1,
        replace: function (item) { //returns what will be put to editor
            var id = item.id;
            if(item.ns === ':'){ //absolute link
                id  = item.ns + id;
            } else if (item.ns) { //relative link
                id = item.ns + ':' + id;
            }
            if(item.type === 'd'){ //namespace
            	setTimeout(function(){$editor.trigger('keyup');},200);
                return '[[' + id;
            } else { //file
            	$editor.data('linksuggest_off',1);
            	
            	setTimeout(function(){$editor.data('linksuggest_off',0);},500);
                return ['[[' + id ,'|'+(item.title?item.title:'') + ']]'];
            }
             
        },
        //header:'test',
        footer:'schließen',
        cache:false
    },{ //Page Section Search
    	appendTo: 'body', 
        match: /\[\[([\w\-\.:]+#[\w\.:]*)$/, 
        index: 1,
        search: function (term, callback) {
        	if($editor.data('linksuggest_off') == 1){
        		callback([]);return;
        	}
            jQuery.post(
                DOKU_BASE + 'lib/exe/ajax.php',
                {call:'plugin_linksuggest',
                    q:term,
                    ns:JSINFO['namespace'],
                    id:JSINFO['id'], 
                },
                function (data) {
                    data=JSON.parse(data);
                    callback(jQuery.map(data.data,function(item){
                        return {'link':data.link,'toc':item};
                    }));
                }
            );
        },
        template:function(item){ //dropdown list
            var toc = item.toc;
            var title = toc.title?' ('+linksuggest_escape(toc.title)+')':'';

            return linksuggest_escape(toc.hid) + title;
        },
        
        replace: function (item) { //returns what will be put to editor
            var link = item.link;
            var toc = item.toc;
            
            $editor.data('linksuggest_off',1);
        	setTimeout(function(){$editor.data('linksuggest_off',0);},500);
        	
            return '[[' + link + '#' + toc.hid;
        },
        cache:false
    }]);
});
