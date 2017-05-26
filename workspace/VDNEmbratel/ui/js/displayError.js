define([ "dijit/registry", "aps/Message", "aps/PageContainer" ],
	function(registry, Message, PageContainer) {
		return function(err, type) {
			
			var errData;

			if(err.response){
				if(err.response.text.length > 0){
					errData = JSON.parse(err.response.text);
				}else{
					errData = {
						message : _('Unable to connect.')
					};
				}
			}else{
				errData = err;
			}

			aps.apsc.cancelProcessing();

			aps.apsc.ready();
	
			var page = registry.byId("page");
			if (!page) {
				page = new PageContainer({
					id : "page"
				});
				page.placeAt(document.body, "first");
			}
			var messages = page.get("messageList");
			/* Remove all current messages from the screen */
			// messages.removeAll();
			/* And display the new message */
			var description = (errData.message ? _(errData.message) + (errData.details ? "<br />" + errData.details:'') : _(err));
			
			if(description.indexOf('Not Found: No appropriate method') >= 0){
			    description =  _('Unable to connect.');
			}
			messages.addChild(new Message({description : description ,type : type || "error"}));
		};
	}
);