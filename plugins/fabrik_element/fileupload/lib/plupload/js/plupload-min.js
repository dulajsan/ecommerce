/*! Fabrik */
!function(){function a(){this.returnValue=!1}function b(){this.cancelBubble=!0}var c,d,e=0,f=[],g={},h={},i={"<":"lt",">":"gt","&":"amp",'"':"quot","'":"#39"},j=/[<>&\"\']/g,k=window.setTimeout,l={};!function(a){var b,c,d,e=a.split(/,/);for(b=0;b<e.length;b+=2)for(d=e[b+1].split(/ /),c=0;c<d.length;c++)h[d[c]]=e[b]}("application/msword,doc dot,application/pdf,pdf,application/pgp-signature,pgp,application/postscript,ps ai eps,application/rtf,rtf,application/vnd.ms-excel,xls xlb,application/vnd.ms-powerpoint,ppt pps pot,application/zip,zip,application/x-shockwave-flash,swf swfl,application/vnd.openxmlformats,pptx xlsx,application/vnd.openxmlformats-officedocument.wordprocessingml.document,docx,audio/mpeg,mpga mpega mp2 mp3,audio/x-wav,wav,audio/mp4,m4a,image/bmp,bmp,image/gif,gif,image/jpeg,jpeg jpg jpe,image/photoshop,psd,image/png,png,image/svg+xml,svg svgz,image/tiff,tiff tif,text/html,htm html xhtml,text/rtf,rtf,video/mpeg,mpeg mpg mpe,video/quicktime,qt mov,video/mp4,mp4,video/x-m4v,m4v,video/x-flv,flv,video/x-ms-wmv,wmv,video/avi,avi,video/webm,webm,video/vnd.rn-realvideo,rv,text/csv,csv,text/plain,asc txt text diff log,application/octet-stream,exe rvt,application/dwg,dwg,application/x-3ds,3ds,application/x-paperport,max");var m={VERSION:"@@version@@",STOPPED:1,STARTED:2,QUEUED:1,UPLOADING:2,FAILED:4,DONE:5,GENERIC_ERROR:-100,HTTP_ERROR:-200,IO_ERROR:-300,SECURITY_ERROR:-400,INIT_ERROR:-500,FILE_SIZE_ERROR:-600,FILE_EXTENSION_ERROR:-601,IMAGE_FORMAT_ERROR:-700,IMAGE_MEMORY_ERROR:-701,IMAGE_DIMENSIONS_ERROR:-702,mimeTypes:h,ua:function(){var a,b,c,d=navigator,e=d.userAgent,f=d.vendor;return a=/WebKit/.test(e),c=a&&-1!==f.indexOf("Apple"),b=window.opera&&window.opera.buildNumber,{windows:-1!==navigator.platform.indexOf("Win"),ie:!a&&!b&&/MSIE/gi.test(e)&&/Explorer/gi.test(d.appName),webkit:a,gecko:!a&&/Gecko/.test(e),safari:c,opera:!!b}}(),extend:function(a){return m.each(arguments,function(b,c){c>0&&m.each(b,function(b,c){a[c]=b})}),a},cleanName:function(a){var b,c;for(c=[/[\300-\306]/g,"A",/[\340-\346]/g,"a",/\307/g,"C",/\347/g,"c",/[\310-\313]/g,"E",/[\350-\353]/g,"e",/[\314-\317]/g,"I",/[\354-\357]/g,"i",/\321/g,"N",/\361/g,"n",/[\322-\330]/g,"O",/[\362-\370]/g,"o",/[\331-\334]/g,"U",/[\371-\374]/g,"u"],b=0;b<c.length;b+=2)a=a.replace(c[b],c[b+1]);return a=a.replace(/\s+/g,"_"),a=a.replace(/[^a-z0-9_\-\.]+/gi,"")},addRuntime:function(a,b){return b.name=a,f[a]=b,f.push(b),b},guid:function(){var a,b=(new Date).getTime().toString(32);for(a=0;5>a;a++)b+=Math.floor(65535*Math.random()).toString(32);return(m.guidPrefix||"p")+b+(e++).toString(32)},buildUrl:function(a,b){var c="";return m.each(b,function(a,b){c+=(c?"&":"")+encodeURIComponent(b)+"="+encodeURIComponent(a)}),c&&(a+=(a.indexOf("?")>0?"&":"?")+c),a},each:function(a,b){var d,e,f;if(a)if(d=a.length,d===c){for(e in a)if(a.hasOwnProperty(e)&&b(a[e],e)===!1)return}else for(f=0;d>f;f++)if(b(a[f],f)===!1)return},formatSize:function(a){return a===c||/\D/.test(a)?m.translate("N/A"):a>1073741824?Math.round(a/1073741824,1)+" GB":a>1048576?Math.round(a/1048576,1)+" MB":a>1024?Math.round(a/1024,1)+" KB":a+" b"},getPos:function(a,b){function c(a){var b,c,d=0,e=0;return a&&(c=a.getBoundingClientRect(),b="CSS1Compat"===i.compatMode?i.documentElement:i.body,d=c.left+b.scrollLeft,e=c.top+b.scrollTop),{x:d,y:e}}var d,e,f,g=0,h=0,i=document;if(a=a,b=b||i.body,a&&a.getBoundingClientRect&&navigator.userAgent.indexOf("MSIE")>0&&8!==i.documentMode)return e=c(a),f=c(b),{x:e.x-f.x,y:e.y-f.y};for(d=a;d&&d!=b&&d.nodeType;)g+=d.offsetLeft||0,h+=d.offsetTop||0,d=d.offsetParent;for(d=a.parentNode;d&&d!=b&&d.nodeType;)g-=d.scrollLeft||0,h-=d.scrollTop||0,d=d.parentNode;return{x:g,y:h}},getSize:function(a){return{w:a.offsetWidth||a.clientWidth,h:a.offsetHeight||a.clientHeight}},parseSize:function(a){var b;return"string"==typeof a&&(a=/^([0-9]+)([mgk]?)$/.exec(a.toLowerCase().replace(/[^0-9mkg]/g,"")),b=a[2],a=+a[1],"g"==b&&(a*=1073741824),"m"==b&&(a*=1048576),"k"==b&&(a*=1024)),a},xmlEncode:function(a){return a?(""+a).replace(j,function(a){return i[a]?"&"+i[a]+";":a}):a},toArray:function(a){var b,c=[];for(b=0;b<a.length;b++)c[b]=a[b];return c},addI18n:function(a){return m.extend(g,a)},translate:function(a){return g[a]||a},isEmptyObj:function(a){if(a===c)return!0;for(var b in a)return!1;return!0},hasClass:function(a,b){var c;return""==a.className?!1:(c=new RegExp("(^|\\s+)"+b+"(\\s+|$)"),c.test(a.className))},addClass:function(a,b){m.hasClass(a,b)||(a.className=""==a.className?b:a.className.replace(/\s+$/,"")+" "+b)},removeClass:function(a,b){var c=new RegExp("(^|\\s+)"+b+"(\\s+|$)");a.className=a.className.replace(c,function(a,b,c){return" "===b&&" "===c?" ":""})},getStyle:function(a,b){return a.currentStyle?a.currentStyle[b]:window.getComputedStyle?window.getComputedStyle(a,null)[b]:void 0},addEvent:function(e,f,g){var h,i,j;j=arguments[3],f=f.toLowerCase(),d===c&&(d="Plupload_"+m.guid()),e.addEventListener?(h=g,e.addEventListener(f,h,!1)):e.attachEvent&&(h=function(){var c=window.event;c.target||(c.target=c.srcElement),c.preventDefault=a,c.stopPropagation=b,g(c)},e.attachEvent("on"+f,h)),e[d]===c&&(e[d]=m.guid()),l.hasOwnProperty(e[d])||(l[e[d]]={}),i=l[e[d]],i.hasOwnProperty(f)||(i[f]=[]),i[f].push({func:h,orig:g,key:j})},removeEvent:function(a,b){var e,f,g;if("function"==typeof arguments[2]?f=arguments[2]:g=arguments[2],b=b.toLowerCase(),a[d]&&l[a[d]]&&l[a[d]][b]){e=l[a[d]][b];for(var h=e.length-1;h>=0&&(e[h].key!==g&&e[h].orig!==f||(a.detachEvent?a.detachEvent("on"+b,e[h].func):a.removeEventListener&&a.removeEventListener(b,e[h].func,!1),e[h].orig=null,e[h].func=null,e.splice(h,1),f===c));h--);if(e.length||delete l[a[d]][b],m.isEmptyObj(l[a[d]])){delete l[a[d]];try{delete a[d]}catch(i){a[d]=c}}}},removeAllEvents:function(a){var b=arguments[1];a[d]!==c&&a[d]&&m.each(l[a[d]],function(c,d){m.removeEvent(a,d,b)})}};m.Uploader=function(a){function b(){var a,b,c=0;if(this.state==m.STARTED){for(b=0;b<i.length;b++)a||i[b].status!=m.QUEUED?c++:(a=i[b],a.status=m.UPLOADING,this.trigger("BeforeUpload",a)&&this.trigger("UploadFile",a));c==i.length&&(this.stop(),this.trigger("UploadComplete",i))}}function d(){var a,b;for(e.reset(),a=0;a<i.length;a++)b=i[a],b.size!==c?(e.size+=b.size,e.loaded+=b.loaded):e.size=c,b.status==m.DONE?e.uploaded++:b.status==m.FAILED?e.failed++:e.queued++;e.size===c?e.percent=i.length>0?Math.ceil(e.uploaded/i.length*100):0:(e.bytesPerSec=Math.ceil(e.loaded/((+new Date-g||1)/1e3)),e.percent=e.size>0?Math.ceil(e.loaded/e.size*100):0)}var e,g,h={},i=[];e=new m.QueueProgress,a=m.extend({chunk_size:0,multipart:!0,multi_selection:!0,file_data_name:"file",filters:[]},a),m.extend(this,{state:m.STOPPED,runtime:"",features:{},files:i,settings:a,total:e,id:m.guid(),init:function(){function e(){var a,b,c,d=j[o++];if(d){if(a=d.getFeatures(),b=n.settings.required_features)for(b=b.split(","),c=0;c<b.length;c++)if(!a[b[c]])return void e();d.init(n,function(b){b&&b.success?(n.features=a,n.runtime=d.name,n.trigger("Init",{runtime:d.name}),n.trigger("PostInit"),n.refresh()):e()})}else n.trigger("Error",{code:m.INIT_ERROR,message:m.translate("Init error.")})}var h,j,l,n=this,o=0;if("function"==typeof a.preinit?a.preinit(n):m.each(a.preinit,function(a,b){n.bind(b,a)}),a.page_url=a.page_url||document.location.pathname.replace(/\/[^\/]+$/g,"/"),/^(\w+:\/\/|\/)/.test(a.url)||(a.url=a.page_url+a.url),a.chunk_size=m.parseSize(a.chunk_size),a.max_file_size=m.parseSize(a.max_file_size),n.bind("FilesAdded",function(b,d){var e,f,g,h=0,j=a.filters;for(j&&j.length&&(g=[],m.each(j,function(a){m.each(a.extensions.split(/,/),function(a){g.push(/^\s*\*\s*$/.test(a)?"\\.*":"\\."+a.replace(new RegExp("["+"/^$.*+?|()[]{}\\".replace(/./g,"\\$&")+"]","g"),"\\$&"))})}),g=new RegExp(g.join("|")+"$","i")),e=0;e<d.length;e++)f=d[e],f.loaded=0,f.percent=0,f.status=m.QUEUED,!g||g.test(f.name)?f.size!==c&&f.size>a.max_file_size?b.trigger("Error",{code:m.FILE_SIZE_ERROR,message:m.translate("File size error."),file:f}):(i.push(f),h++):b.trigger("Error",{code:m.FILE_EXTENSION_ERROR,message:m.translate("File extension error."),file:f});return h?void k(function(){n.trigger("QueueChanged"),n.refresh()},1):!1}),a.unique_names&&n.bind("UploadFile",function(a,b){var c=b.name.match(/\.([^.]+)$/),d="tmp";c&&(d=c[1]),b.target_name=b.id+"."+d}),n.bind("UploadProgress",function(a,b){b.percent=b.size>0?Math.ceil(b.loaded/b.size*100):100,d()}),n.bind("StateChanged",function(a){if(a.state==m.STARTED)g=+new Date;else if(a.state==m.STOPPED)for(h=a.files.length-1;h>=0;h--)a.files[h].status==m.UPLOADING&&(a.files[h].status=m.QUEUED,d())}),n.bind("QueueChanged",d),n.bind("Error",function(a,c){c.file&&(c.file.status=m.FAILED,d(),a.state==m.STARTED&&k(function(){b.call(n)},1))}),n.bind("FileUploaded",function(a,c){c.status=m.DONE,c.loaded=c.size,a.trigger("UploadProgress",c),k(function(){b.call(n)},1)}),a.runtimes)for(j=[],l=a.runtimes.split(/\s?,\s?/),h=0;h<l.length;h++)f[l[h]]&&j.push(f[l[h]]);else j=f;e(),"function"==typeof a.init?a.init(n):m.each(a.init,function(a,b){n.bind(b,a)})},refresh:function(){this.trigger("Refresh")},start:function(){this.state!=m.STARTED&&(this.state=m.STARTED,this.trigger("StateChanged"),b.call(this))},stop:function(){this.state!=m.STOPPED&&(this.state=m.STOPPED,this.trigger("StateChanged"))},getFile:function(a){var b;for(b=i.length-1;b>=0;b--)if(i[b].id===a)return i[b]},removeFile:function(a){var b;for(b=i.length-1;b>=0;b--)if(i[b].id===a.id)return this.splice(b,1)[0]},splice:function(a,b){var d;return d=i.splice(a===c?0:a,b===c?i.length:b),this.trigger("FilesRemoved",d),this.trigger("QueueChanged"),d},trigger:function(a){var b,c,d=h[a.toLowerCase()];if(d)for(c=Array.prototype.slice.call(arguments),c[0]=this,b=0;b<d.length;b++)if(d[b].func.apply(d[b].scope,c)===!1)return!1;return!0},hasEventListener:function(a){return!!h[a.toLowerCase()]},bind:function(a,b,c){var d;a=a.toLowerCase(),d=h[a]||[],d.push({func:b,scope:c||this}),h[a]=d},unbind:function(a){a=a.toLowerCase();var b,d=h[a],e=arguments[1];if(d){if(e!==c){for(b=d.length-1;b>=0;b--)if(d[b].func===e){d.splice(b,1);break}}else d=[];d.length||delete h[a]}},unbindAll:function(){var a=this;m.each(h,function(b,c){a.unbind(c)})},destroy:function(){this.trigger("Destroy"),this.unbindAll()}})},m.File=function(a,b,c){var d=this;d.id=a,d.name=b,d.size=c,d.loaded=0,d.percent=0,d.status=0},m.Runtime=function(){this.getFeatures=function(){},this.init=function(){}},m.QueueProgress=function(){var a=this;a.size=0,a.loaded=0,a.uploaded=0,a.failed=0,a.queued=0,a.percent=0,a.bytesPerSec=0,a.reset=function(){a.size=a.loaded=a.uploaded=a.failed=a.queued=a.percent=a.bytesPerSec=0}},m.runtimes={},window.plupload=m}();