function buildCombo(at, model, id, seq, res, valueArray, defaValue){
    var options=[];
    // traverse json object where property names (chave) will be used as values for select widget
    // this property is another JS object with a property "name" for human readable lables
    for( var chave in valueArray ){
        if( valueArray[chave] == defaValue ){
            options.push({label:valueArray[chave].name, value:chave, selected:true });
        }
        else {
            for( var ix in valueArray[chave].allow ){
                if( valueArray[chave].allow[ix] == res){
                    options.push({label:valueArray[chave].name, value:chave});
                    break;
                }
            }
        }
    }
    return ["aps/Select",{id:id+'_'+seq, value: at(model,id+'_'+seq), options:options}];
}



require(["aps/load",
         "aps/Memory",
         "dojox/mvc/at",
         "aps/ready!"],
    function(load, Memory, at) {
        "use strict";
        var framRats = {
            frfs:   {name: "Follow Source",numer:null,   denom:null, allow:["1080p","720p","480p","360p","240p"]},
            fr30:   {name: "30",           numer:30,     denom:1,    allow:["1080p","720p"]},
            fr2997: {name: "29.97",        numer:30000,  denom:1001, allow:["1080p","720p"]},
            fr25:   {name: "25",           numer:25,     denom:1,    allow:["1080p","720p","480p"]},
            fr24:   {name: "24",           numer:24,     denom:1,    allow:["1080p","720p","480p"]},
            fr2397: {name: "23.976",       numer:24000,  denom:1001, allow:["1080p","720p","480p"]},
            fr15:   {name: "15",           numer:15,     denom:1,    allow:["480p", "360p","240p"]},
            fr10:   {name: "10",           numer:10,     denom:1,    allow:["480p", "360p","240p"]}
        };


        var vidBitRats = {
            "vbr5M":   {"name":"5Mbps",  "value":"5000000","allow":["1080p"]},
            "vbr4_5M": {"name":"4.5Mbps","value":"4500000","allow":["1080p"]},
            "vbr4M":   {"name":"4Mbps",  "value":"4000000","allow":["1080p"]},
            "vbr3_2M": {"name":"3.2Mbps","value":"3200000","allow":[]},
            "vbr3_5M": {"name":"3.5Mbps","value":"3500000","allow":["1080p","720p"]},
            "vbr3M":   {"name":"3Mbps",  "value":"3000000","allow":["1080p","720p"]},
            "vbr2_5M": {"name":"2.5Mbps","value":"2500000","allow":["720p"]},
            "vbr2M":   {"name":"2Mbps",  "value":"2000000","allow":["720p"]},
            "vbr1_8M": {"name":"1.8Mbps","value":"1800000","allow":[]},
            "vbr1_5M": {"name":"1.5Mbps","value":"1500000","allow":["720p"]},
            "vbr1_2M": {"name":"1.2Mbps","value":"1200000","allow":["480p"]},
            "vbr1M":   {"name":"1Mbps",  "value":"1000000","allow":["480p"]},
            "vbr800K": {"name":"800Kbps","value": "800000","allow":["480p"]},
            "vbr760K": {"name":"760Kbps","value": "760000","allow":["480p"]},
            "vbr720K": {"name":"720Kbps","value": "720000","allow":["480p"]},
            "vbr650K": {"name":"650Kbps","value": "650000","allow":["360p"]},
            "vbr600K": {"name":"600Kbps","value": "600000","allow":["360p"]},
            "vbr500K": {"name":"500Kbps","value": "500000","allow":["360p"]},
            "vbr480K": {"name":"480Kbps","value": "480000","allow":["360p"]},
            "vbr400K": {"name":"400Kbps","value": "400000","allow":["360p"]},
            "vbr350K": {"name":"350Kbps","value": "350000","allow":["240p"]},
            "vbr300K": {"name":"300Kbps","value": "300000","allow":["240p"]},
            "vbr250K": {"name":"250Kbps","value": "250000","allow":["240p"]},
            "vbr200K": {"name":"200Kbps","value": "200000","allow":["240p"]},
            "vbr100K": {"name":"100Kbps","value": "100000","allow":["240p"]},
            "vbr56K":  {"name":"56Kbps", "value":  "56000","allow":["240p"]}
        };

        var audBitRats = {
            abr320K: {name: "320Kbps", value: "320000", allow:["1080p","720p"] },
            abr256K: {name: "256Kbps", value: "256000", allow:["1080p","720p","480p"]},
            abr192K: {name: "192Kbps", value: "192000", allow:["1080p","720p","480p"]},
            abr128K: {name: "128Kbps", value: "128000", allow:["1080p","720p","480p"] },
            abr96K:  {name: "96Kbps",  value:  "96000", allow:["1080p","720p","480p","360p"]},
            abr80K:  {name: "80Kbps",  value:  "80000", allow:["480p", "360p"]},
            abr64K:  {name: "64Kbps",  value:  "64000", allow:["360p", "240p"]},
            abr32K:  {name: "32Kbps",  value:  "32000", allow:["360p", "240p"]},
            abr24K:  {name: "24Kbps",  value:  "24000", allow:["240p"]}
        };

        var resol4x3={
            proportion:"4:3",
            r1080p:{name:"1080p",variants:[{w:1600,h:1200},{w:1540,h:1155},{w:1440,h:1080},{w:1280,h: 960},{w:1024,h: 768} ]},
            r720p: {name: "720p",variants:[{w: 960,h: 720},{w:1024,h: 768},{w: 920,h: 690},{w: 860,h: 645},{w: 720,h: 540}]},
            r480p: {name: "480p",variants:[{w: 640,h: 480},{w: 580,h: 435},{w: 460,h: 345}]},
            r360p: {name: "360p",variants:[{w: 480,h: 360},{w: 420,h: 315},{w: 380,h: 285},{w: 360,h: 270},{w: 320,h: 240}]},
            r240p: {name: "240p",variants:[{w: 320,h: 240},{w: 280,h: 210},{w: 180,h: 135},{w: 160,h: 120}]}
            
        };
 
        var resol16x9 = {
            proportion:"16:9",
            r1080p:{name:"1080p",variants:[{w:1920,h:1080},{w:1840,h:1035},{w:1760,h: 990},{w:1680,h: 945},{w:1520,h: 855}]},
            r720p: {name: "720p",variants:[{w:1280,h: 720},{w:1120,h: 630},{w:1040,h: 585},{w: 960,h: 540}]},
            r480p: {name: "480p",variants:[{w: 960,h: 540},{w: 880,h: 495},{w: 720,h: 405},{w: 640,h: 360}]},
            r360p: {name: "360p",variants:[{w: 640,h: 360},{w: 560,h: 315},{w: 480,h: 270}]},
            r240p: {name: "240p",variants:[{w: 480,h: 270},{w: 320,h: 180},{w: 240,h: 135},{w: 160,h:  90}]}};

        var videoParms = {
            r4x3: [
                { name:"Premium-4:3-1080p",  res:resol4x3.r1080p, vbr:vidBitRats.vbr5M,   fr:framRats.frfs, abr:audBitRats.abr96K},
                { name:"Premium-4:3-720p-1", res:resol4x3.r720p,  vbr:vidBitRats.vbr3_5M, fr:framRats.frfs, abr:audBitRats.abr96K},
                { name:"Premium-4:3-720p-2", res:resol4x3.r720p,  vbr:vidBitRats.vbr2M,   fr:framRats.frfs, abr:audBitRats.abr96K},
                { name:"Premium-4:3-480p-1", res:resol4x3.r480p,  vbr:vidBitRats.vbr1_2M, fr:framRats.frfs, abr:audBitRats.abr96K},
                { name:"Premium-4:3-480p-2", res:resol4x3.r480p,  vbr:vidBitRats.vbr800K, fr:framRats.frfs, abr:audBitRats.abr96K},
                { name:"Premium-4:3-360p-1", res:resol4x3.r360p,  vbr:vidBitRats.vbr650K, fr:framRats.fr15, abr:audBitRats.abr64K},
                { name:"Premium-4:3-360p-2", res:resol4x3.r360p,  vbr:vidBitRats.vbr450K, fr:framRats.fr15, abr:audBitRats.abr64K},
                { name:"Premium-4:3-240p",   res:resol4x3.r240p,  vbr:vidBitRats.vbr240K, fr:framRats.fr15, abr:audBitRats.abr64K}
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
                { name:"Premium-16:9-1080p", res:resol16x9.r1080p,vbr:vidBitRats.vbr3_2M, fr:framRats.frfs, abr:audBitRats.abr96K},
                { name:"Premium-16:9-720p",  res:resol16x9.r720p, vbr:vidBitRats.vbr1_8M, fr:framRats.frfs, abr:audBitRats.abr96K},
                { name:"Premium-16:9-480p",  res:resol16x9.r480p, vbr:vidBitRats.vbr1M,   fr:framRats.frfs, abr:audBitRats.abr96K},
                { name:"Premium-16:9-360p",  res:resol16x9.r360p, vbr:vidBitRats.vbr650K, fr:framRats.fr15, abr:audBitRats.abr64K},
                { name:"Premium-16:9-240p",  res:resol16x9.r240p, vbr:vidBitRats.vbr250K, fr:framRats.fr15, abr:audBitRats.abr64K}
            ]};

        var model={};

        var containers=[];

        var params=standardParms.r4x3;
        var paramLength=params.length;

        for( var i=0; i<paramLength; ++i ) {
            var resolutions=[];
            var variants = params[i].res.variants;
            var varLen = variants.length;
            for( var j=0; j<varLen; ++j ) {
                var resText=variants[j].w + 'x' + variants[j].h;
                resolutions.push({label:resText, value:resText});
            }
            var res=params[i].res.name;
            var id="str_"+res+"_"+i;
            containers.push(
                ["aps/Container",{label:params[i].name},[
                    ["aps/Output",{content:" Res:"}],
                 
                     ["aps/Select",{id:id, value: at(model,"res_"+id),options:resolutions}],
                     ["aps/Output",{content:" FR:"}],
                     buildCombo(model, "fr", i, res, framRats, params[i].fr)
                     ["aps/Output",{content:" VBR:"}],
					 buildCombo( model, "vbr", i, res, vidBitRats, params[i].vbr),
                     ["aps/Output",{content:" ABR: "}],
                     buildCombo(model, "abr", i, res, audBitRats, params[i].abr)
                 ]]);
        }
        
        load(["aps/PageContainer", [
            ["aps/FieldSet", {
                    title: "Premium Transcoder Parameter Customization",
                    description: "Each stream may have its resolution, frame rate, video and audio bit rate modified."
                }, containers
           ]]]);
    });

