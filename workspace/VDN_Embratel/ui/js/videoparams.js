buildCombo = function (at, model, parm, ix, res, valueArray, defaValue)
{
	// model will hold defaults/selected for parms(parm) of each stream(ix)
    model[parm][ix]="";
    var options=[];
    // traverse json object where property names (chave) will be used as values for select widget
    // this property is another JS object with a property "name" for human readable lables
    for( var chave in valueArray )
    {
        if( valueArray[chave] == defaValue )
        {
            options.push({label:valueArray[chave].name, value:chave, selected:true });
            model[parm][ix]=chave;
        }
        else
        {
            for( var i in valueArray[chave].allow )
            {
                if( valueArray[chave].allow[i] == res)
                {
                    options.push({label:valueArray[chave].name, value:chave});
                    break;
                }
            }
        }
    }
    return ["aps/Select",{id:parm+ix, value: at(model[parm],ix), options:options}];
}

    require([       "aps/ResourceStore",
                    "aps/load",
                    "aps/Memory",
                    "aps/WizardData",
                   "dojo/when",
                  "dijit/registry",
              "dojox/mvc/at",
              "dojox/mvc/getStateful",
              "dojox/mvc/getPlainValue",
            "dojo/text!./frameRates.json",
            "dojo/text!./videoBitRates.json",
            "dojo/text!./audioBitRates.json",
            "dojo/text!./resol4x3.json",
            "dojo/text!./resol16x9.json",
                    "aps/ready!"],
        function(Store, load, Memory, WizardData, when, registry, at, getStateful, getPlainValue,
        		frameRates, videoBitRates, audioBitRates, resol4x3j, resol16x9j)
        {
    		"use strict";

            var model = getStateful(WizardData.get()),
                framRats   = JSON.parse(frameRates),
        	    vidBitRats = JSON.parse(videoBitRates),
        	    audBitRats = JSON.parse(audioBitRates),
        	    resol4x3   = JSON.parse(resol4x3j),
        	    resol16x9  = JSON.parse(resol16x9j),
        	    store = new Store({
                    apsType: "http://embratel.com.br/app/VDN_Embratel/job/1.0",
                    target: "/aps/2/resources/" + aps.context.vars.context.aps.id + "/jobs"
                });

            var premiumParms = {
                r4x3: [
                    { name:"Premium-4:3-1080p",  res:resol4x3.r1080p, vbr:vidBitRats.vbr5M,   fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Premium-4:3-720p-1", res:resol4x3.r720p,  vbr:vidBitRats.vbr3_5M, fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Premium-4:3-720p-2", res:resol4x3.r720p,  vbr:vidBitRats.vbr2M,   fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Premium-4:3-480p-1", res:resol4x3.r480p,  vbr:vidBitRats.vbr1_2M, fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Premium-4:3-480p-2", res:resol4x3.r480p,  vbr:vidBitRats.vbr800K, fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Premium-4:3-360p-1", res:resol4x3.r360p,  vbr:vidBitRats.vbr650K, fr:framRats.fr15, abr:audBitRats.abr64K},
                    { name:"Premium-4:3-360p-2", res:resol4x3.r360p,  vbr:vidBitRats.vbr480K, fr:framRats.fr15, abr:audBitRats.abr64K},
                    { name:"Premium-4:3-240p",   res:resol4x3.r240p,  vbr:vidBitRats.vbr250K, fr:framRats.fr15, abr:audBitRats.abr64K}
                ],
                r16x9: [
                    { name:"Premium-16:9-1080p", res:resol16x9.r1080p,vbr:vidBitRats.vbr5M,   fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Premium-16:9-720p-1",res:resol16x9.r720p, vbr:vidBitRats.vbr3_5M, fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Premium-16:9-720p-2",res:resol16x9.r720p, vbr:vidBitRats.vbr2M,   fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Premium-16:9-480p-1",res:resol16x9.r480p, vbr:vidBitRats.vbr1_2M, fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Premium-16:9-480p-2",res:resol16x9.r480p, vbr:vidBitRats.vbr800K, fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Premium-16:9-360p-1",res:resol16x9.r360p, vbr:vidBitRats.vbr650K, fr:framRats.fr15, abr:audBitRats.abr64K},
                    { name:"Premium-16:9-360p-2",res:resol16x9.r360p, vbr:vidBitRats.vbr480K, fr:framRats.fr15, abr:audBitRats.abr64K},
                    { name:"Premium-16:9-240p",  res:resol16x9.r240p, vbr:vidBitRats.vbr250K, fr:framRats.fr15, abr:audBitRats.abr64K}
                ]};

            var standardParms = {
                r4x3: [
                    { name:"Standard-4:3-1080p", res:resol4x3.r1080p, vbr:vidBitRats.vbr3_2M, fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Standard-4:3-720p",  res:resol4x3.r720p,  vbr:vidBitRats.vbr1_8M, fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Standard-4:3-480p",  res:resol4x3.r480p,  vbr:vidBitRats.vbr1M,   fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Standard-4:3-360p",  res:resol4x3.r360p,  vbr:vidBitRats.vbr650K, fr:framRats.fr15, abr:audBitRats.abr64K},
                    { name:"Standard-4:3-240p",  res:resol4x3.r240p,  vbr:vidBitRats.vbr250K, fr:framRats.fr15, abr:audBitRats.abr64K}
                ],
                r16x9: [
                    { name:"Standard-16:9-1080p", res:resol16x9.r1080p,vbr:vidBitRats.vbr3_2M, fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Standard-16:9-720p",  res:resol16x9.r720p, vbr:vidBitRats.vbr1_8M, fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Standard-16:9-480p",  res:resol16x9.r480p, vbr:vidBitRats.vbr1M,   fr:framRats.frfs, abr:audBitRats.abr96K},
                    { name:"Standard-16:9-360p",  res:resol16x9.r360p, vbr:vidBitRats.vbr650K, fr:framRats.fr15, abr:audBitRats.abr64K},
                    { name:"Standard-16:9-240p",  res:resol16x9.r240p, vbr:vidBitRats.vbr250K, fr:framRats.fr15, abr:audBitRats.abr64K}
                ]};

            var planParms;
            if(model.premium) {
                planParms = premiumParms;
            } else {
                planParms = standardParms;
            }
            
            var params;
            if(model.screen_format == "4:3") {
                params = planParms.r4x3;
            } else {
                params = planParms.r16x9;
            }

            var paramLength=params.length;

            // loop pelos formatos de saida (5/std 8xPremjhgfj)
            // armazena resolução/framerate/bitrate A/V em objetos 
            var containers=[];
            for( var i=0; i<paramLength; ++i ) {
                var resolutions=[];
                var variants = params[i].res.variants;
                var varLen = variants.length;
                model["res"][i] = resText=variants[0].w + 'x' + variants[0].h;
                for( var j=0; j<varLen; ++j ) {
                    var resText=variants[j].w + 'x' + variants[j].h;
                    resolutions.push({label:resText, value:resText});
                }
                var res=params[i].res.name;
                var id="str_"+res+"_"+i;
                containers.push(
                    ["aps/Container",{label:params[i].name},[
                        ["aps/Output",{content:" Res:"}],
                        ["aps/Select",{id:"res_"+i, value: at(model["resolutions"],i),options:resolutions}],
                        ["aps/Output",{content:" FR:"}],
                        buildCombo(at, model, "framerates", i, res, framRats, params[i].fr),
                        ["aps/Output",{content:" VBR:"}],
                        buildCombo(at, model, "video_bitrates", i, res, vidBitRats, params[i].vbr),
                        ["aps/Output",{content:" ABR: "}],
                        buildCombo(at, model, "audio_bitrates", i, res, audBitRats, params[i].abr)
                    ]]
                );
            }
            var nameLabel, mainView, prevView, nextView, mainTitle, mainDescr;
            if(model.type == 'vod'){
                nameLabel = _("Chanel Name");
                mainView = "jobs";
                prevView = "job-new-1";
                nextView = "job-new-last";
            }
            else {
                nameLabel = _("Content Name");
                mainView = "channels";
                prevView = "channel-new-1";
                nextView = "channel-new-last";
            }
            
            if( model.premium ) {
                mainTitle = _("Premium Transcoder Parameter Customization");
                mainDescr = _("You may change each stream's Resolution, Frame Rate, Video Bit Rate and Audio Bit Rate for the transcoder.");
            }
            else {
                mainTitle = _("Transcoder Parameters");
                mainDescr = _("Resolutions, Frame Rates, Video Bit Rates and Audio Bit Rates that will be used in transcoding.");
            }

            var widgets =["aps/PageContainer", { id: "page" }, [
                ["aps/FieldSet", {title:true}, [
                    ["aps/Output", { label: nameLabel, value: at(model, "name") }]]],
                ["aps/FieldSet", {title:true}, []],
                ["aps/FieldSet", {
                    title: mainTitle,
                    description: mainDescr
                },containers
                ]]];
            load(widgets).then(function(){

            	/* Create handlers for the navigation buttons */

	            aps.app.onSubmit = function() {
	                var page = registry.byId("page");
	                if (!page.validate()) {
	                    aps.apsc.cancelProcessing();
	                    return;
	                }
	                when(store.put(getPlainValue(model)),
	                    function(){ aps.apsc.gotoView("jobs"); },
	                    displayError
	                );
	            };

	            aps.app.onPrev = function() {
	                aps.apsc.prev();
                };
            });
    	} // End of function
    ); // End of require
	</script>
</head>
<body>
</body>
</html>

