define(["dojox/mvc/at"],
    function (at)
    {
        return function (model, frameRates, videoBitRates, audioBitRates, resol4x3j, resol16x9j )
        {
            "use strict";
		    var framRats   = JSON.parse(frameRates),
			    vidBitRats = JSON.parse(videoBitRates),
			    audBitRats = JSON.parse(audioBitRates),
			    resol4x3   = JSON.parse(resol4x3j),
			    resol16x9  = JSON.parse(resol16x9j);
			var buildCombo = function ( model, parm, ix, res, valueArray, defaValue, disabled)
				{
					// model will hold defaults/selected for parms(parm) of each stream(ix)
					model[parm][ix]="";
					var options=[];
					// traverse json object creating select widget with name/value
					// this property is another JS object with a property "name" for human readable labels
					for( var chave in valueArray )
					{
						var obj = valueArray[chave];
						if( obj == defaValue )
						{
							options.push({label: obj.name, value:obj.value, selected:true });
							model[parm][ix]=obj.value;
						}
						else
						{
							for( var i in obj.allow )
							{
								if( obj.allow[i] == res)
								{
									options.push({label:obj.name, value:obj.value});
									break;
								}
							}
						}
					}
					return ["aps/Select",{id:parm+ix, value: at(model[parm],ix),
						disabled: disabled, options:options}];
				},
				premiumParms =
				{
					r4x3_rafa: [
		                { name:"Premium-4:3-1080p",  res:resol4x3.r1080p, vbr:vidBitRats.vbr5M,   fr:framRats.frfs, abr:audBitRats.abr96K},
		                { name:"Premium-4:3-720p-1", res:resol4x3.r720p,  vbr:vidBitRats.vbr3_5M, fr:framRats.frfs, abr:audBitRats.abr96K},
		                { name:"Premium-4:3-720p-2", res:resol4x3.r720p,  vbr:vidBitRats.vbr2M,   fr:framRats.frfs, abr:audBitRats.abr96K},
		                { name:"Premium-4:3-480p-1", res:resol4x3.r480p,  vbr:vidBitRats.vbr1_2M, fr:framRats.frfs, abr:audBitRats.abr96K},
		                { name:"Premium-4:3-480p-2", res:resol4x3.r480p,  vbr:vidBitRats.vbr800K, fr:framRats.frfs, abr:audBitRats.abr96K},
		                { name:"Premium-4:3-360p-1", res:resol4x3.r360p,  vbr:vidBitRats.vbr650K, fr:framRats.fr15, abr:audBitRats.abr64K},
		                { name:"Premium-4:3-360p-2", res:resol4x3.r360p,  vbr:vidBitRats.vbr480K, fr:framRats.fr15, abr:audBitRats.abr64K},
		                { name:"Premium-4:3-240p",   res:resol4x3.r240p,  vbr:vidBitRats.vbr250K, fr:framRats.fr15, abr:audBitRats.abr64K}
		            ],
		            r16x9_rafa: [
		                { name:"Premium-16:9-1080p", res:resol16x9.r1080p,vbr:vidBitRats.vbr5M,   fr:framRats.frfs, abr:audBitRats.abr96K},
		                { name:"Premium-16:9-720p-1",res:resol16x9.r720p, vbr:vidBitRats.vbr3_5M, fr:framRats.frfs, abr:audBitRats.abr96K},
		                { name:"Premium-16:9-720p-2",res:resol16x9.r720p, vbr:vidBitRats.vbr2M,   fr:framRats.frfs, abr:audBitRats.abr96K},
		                { name:"Premium-16:9-480p-1",res:resol16x9.r480p, vbr:vidBitRats.vbr1_2M, fr:framRats.frfs, abr:audBitRats.abr96K},
		                { name:"Premium-16:9-480p-2",res:resol16x9.r480p, vbr:vidBitRats.vbr800K, fr:framRats.frfs, abr:audBitRats.abr96K},
		                { name:"Premium-16:9-360p-1",res:resol16x9.r360p, vbr:vidBitRats.vbr650K, fr:framRats.fr15, abr:audBitRats.abr64K},
		                { name:"Premium-16:9-360p-2",res:resol16x9.r360p, vbr:vidBitRats.vbr480K, fr:framRats.fr15, abr:audBitRats.abr64K},
		                { name:"Premium-16:9-240p",  res:resol16x9.r240p, vbr:vidBitRats.vbr250K, fr:framRats.fr15, abr:audBitRats.abr64K}
		            ],
					r4x3: [
		                { name:"Premium-4:3-1080p",  res:resol4x3.r1080p, vbr:vidBitRats.vbr5M,   fr:framRats.fr30, abr:audBitRats.abr96K},
		                { name:"Premium-4:3-720p-1", res:resol4x3.r720p,  vbr:vidBitRats.vbr3_5M, fr:framRats.fr30, abr:audBitRats.abr96K},
		                { name:"Premium-4:3-720p-2", res:resol4x3.r720p,  vbr:vidBitRats.vbr2M,   fr:framRats.fr30, abr:audBitRats.abr96K},
		                { name:"Premium-4:3-480p-1", res:resol4x3.r480p,  vbr:vidBitRats.vbr1_2M, fr:framRats.fr25, abr:audBitRats.abr96K},
		                { name:"Premium-4:3-480p-2", res:resol4x3.r480p,  vbr:vidBitRats.vbr800K, fr:framRats.fr25, abr:audBitRats.abr96K},
		                { name:"Premium-4:3-360p-1", res:resol4x3.r360p,  vbr:vidBitRats.vbr650K, fr:framRats.fr15, abr:audBitRats.abr64K},
		                { name:"Premium-4:3-360p-2", res:resol4x3.r360p,  vbr:vidBitRats.vbr480K, fr:framRats.fr15, abr:audBitRats.abr64K},
		                { name:"Premium-4:3-240p",   res:resol4x3.r240p,  vbr:vidBitRats.vbr250K, fr:framRats.fr15, abr:audBitRats.abr64K}
		            ],
		            r16x9: [
		                { name:"Premium-16:9-1080p", res:resol16x9.r1080p,vbr:vidBitRats.vbr5M,   fr:framRats.fr30, abr:audBitRats.abr96K},
		                { name:"Premium-16:9-720p-1",res:resol16x9.r720p, vbr:vidBitRats.vbr3_5M, fr:framRats.fr30, abr:audBitRats.abr96K},
		                { name:"Premium-16:9-720p-2",res:resol16x9.r720p, vbr:vidBitRats.vbr2M,   fr:framRats.fr30, abr:audBitRats.abr96K},
		                { name:"Premium-16:9-480p-1",res:resol16x9.r480p, vbr:vidBitRats.vbr1_2M, fr:framRats.fr25, abr:audBitRats.abr96K},
		                { name:"Premium-16:9-480p-2",res:resol16x9.r480p, vbr:vidBitRats.vbr800K, fr:framRats.fr25, abr:audBitRats.abr96K},
		                { name:"Premium-16:9-360p-1",res:resol16x9.r360p, vbr:vidBitRats.vbr650K, fr:framRats.fr15, abr:audBitRats.abr64K},
		                { name:"Premium-16:9-360p-2",res:resol16x9.r360p, vbr:vidBitRats.vbr480K, fr:framRats.fr15, abr:audBitRats.abr64K},
		                { name:"Premium-16:9-240p",  res:resol16x9.r240p, vbr:vidBitRats.vbr250K, fr:framRats.fr15, abr:audBitRats.abr64K}
		            ]
				},
		        standardParms =
		        {
		            r4x3: [
		                { name:"Standard-4:3-1080p", res:resol4x3.r1080p, vbr:vidBitRats.vbr3_2M, fr:framRats.fr30, abr:audBitRats.abr96K},
		                { name:"Standard-4:3-720p",  res:resol4x3.r720p,  vbr:vidBitRats.vbr1_8M, fr:framRats.fr30, abr:audBitRats.abr96K},
		                { name:"Standard-4:3-480p",  res:resol4x3.r480p,  vbr:vidBitRats.vbr1M,   fr:framRats.fr25, abr:audBitRats.abr96K},
		                { name:"Standard-4:3-360p",  res:resol4x3.r360p,  vbr:vidBitRats.vbr650K, fr:framRats.fr15, abr:audBitRats.abr64K},
		                { name:"Standard-4:3-240p",  res:resol4x3.r240p,  vbr:vidBitRats.vbr250K, fr:framRats.fr15, abr:audBitRats.abr64K}
		            ],
		            r16x9: [
		                { name:"Standard-16:9-1080p", res:resol16x9.r1080p,vbr:vidBitRats.vbr3_2M, fr:framRats.fr30, abr:audBitRats.abr96K},
		                { name:"Standard-16:9-720p",  res:resol16x9.r720p, vbr:vidBitRats.vbr1_8M, fr:framRats.fr30, abr:audBitRats.abr96K},
		                { name:"Standard-16:9-480p",  res:resol16x9.r480p, vbr:vidBitRats.vbr1M,   fr:framRats.fr25, abr:audBitRats.abr96K},
		                { name:"Standard-16:9-360p",  res:resol16x9.r360p, vbr:vidBitRats.vbr650K, fr:framRats.fr15, abr:audBitRats.abr64K},
		                { name:"Standard-16:9-240p",  res:resol16x9.r240p, vbr:vidBitRats.vbr250K, fr:framRats.fr15, abr:audBitRats.abr64K}
		            ]
		        },
		        planParms,
		        disabled = true;
                
			if(model.premium) {
				disabled = false;
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
		    // loop pelos formatos de saida (5xstd 8xPrem)
		    // armazena resolução/framerate/bitrate A/V em objetos 
		    var containers=[], resText;
			model["resolutions"] = [];
			model["framerates"] = [];
			model["video_bitrates"] = [];
			model["audio_bitrates"] = [];
		    for( var i=0; i<paramLength; ++i ) {
		        var resolutions=[];
		        var variants = params[i].res.variants;
		        var varLen = variants.length;
		        model["resolutions"][i] = resText=variants[0].w + 'x' + variants[0].h;
		        for( var j=0; j<varLen; ++j ) {
		            resText=variants[j].w + 'x' + variants[j].h;
		            resolutions.push({label:resText, value:resText});
		        }
		        var res=params[i].res.name;
		        containers.push(
		            ["aps/Container",{label:params[i].name},[
		                ["aps/Output",{content:" Res:"}],
                        ["aps/Select",{id:"res_"+i, value: at(model["resolutions"],i),
                        disabled:disabled, options:resolutions}],
		                ["aps/Output",{content:" FR:"}],
		                buildCombo(model, "framerates", i, res, framRats, params[i].fr, false),
		                ["aps/Output",{content:" VBR:"}],
		                buildCombo(model, "video_bitrates", i, res, vidBitRats, params[i].vbr, disabled),
		                ["aps/Output",{content:" ABR: "}],
		                buildCombo(model, "audio_bitrates", i, res, audBitRats, params[i].abr, disabled)
		            ]]
		        );
		    }
		    var mainTitle, mainDescr;
		    
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
		            ["aps/Output", { label: _("Name"), value: at(model, "name") }]]],
		        ["aps/FieldSet", {title:true}, []],
		        ["aps/FieldSet", {
		                title: mainTitle,
		                description: mainDescr
		            },containers
		            ]]];
		    return widgets;
	    };
	}
);
