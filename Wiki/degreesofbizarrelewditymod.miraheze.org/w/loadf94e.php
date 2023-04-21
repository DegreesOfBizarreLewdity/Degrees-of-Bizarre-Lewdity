function isCompatible(ua){return!!((function(){'use strict';return!this&&Function.prototype.bind;}())&&'querySelector'in document&&'localStorage'in window&&!ua.match(/MSIE 10|NetFront|Opera Mini|S40OviBrowser|MeeGo|Android.+Glass|^Mozilla\/5\.0 .+ Gecko\/$|googleweblight|PLAYSTATION|PlayStation/));}if(!isCompatible(navigator.userAgent)){document.documentElement.className=document.documentElement.className.replace(/(^|\s)client-js(\s|$)/,'$1client-nojs$2');while(window.NORLQ&&NORLQ[0]){NORLQ.shift()();}NORLQ={push:function(fn){fn();}};RLQ={push:function(){}};}else{if(window.performance&&performance.mark){performance.mark('mwStartup');}(function(){'use strict';var con=window.console;function logError(topic,data){if(con.log){var e=data.exception;var msg=(e?'Exception':'Error')+' in '+data.source+(data.module?' in module '+data.module:'')+(e?':':'.');con.log(msg);if(e&&con.warn){con.warn(e);}}}function Map(){this.values=Object.create(null);}Map.prototype={constructor:Map,get:function(
selection,fallback){if(arguments.length<2){fallback=null;}if(typeof selection==='string'){return selection in this.values?this.values[selection]:fallback;}var results;if(Array.isArray(selection)){results={};for(var i=0;i<selection.length;i++){if(typeof selection[i]==='string'){results[selection[i]]=selection[i]in this.values?this.values[selection[i]]:fallback;}}return results;}if(selection===undefined){results={};for(var key in this.values){results[key]=this.values[key];}return results;}return fallback;},set:function(selection,value){if(arguments.length>1){if(typeof selection==='string'){this.values[selection]=value;return true;}}else if(typeof selection==='object'){for(var key in selection){this.values[key]=selection[key];}return true;}return false;},exists:function(selection){return typeof selection==='string'&&selection in this.values;}};var log=function(){};log.warn=con.warn?Function.prototype.bind.call(con.warn,con):function(){};var mw={now:function(){var perf=window.performance;
var navStart=perf&&perf.timing&&perf.timing.navigationStart;mw.now=navStart&&perf.now?function(){return navStart+perf.now();}:Date.now;return mw.now();},trackQueue:[],track:function(topic,data){mw.trackQueue.push({topic:topic,data:data});},trackError:function(topic,data){mw.track(topic,data);logError(topic,data);},Map:Map,config:new Map(),messages:new Map(),templates:new Map(),log:log};window.mw=window.mediaWiki=mw;}());(function(){'use strict';var StringSet,store,hasOwn=Object.hasOwnProperty;function defineFallbacks(){StringSet=window.Set||function(){var set=Object.create(null);return{add:function(value){set[value]=true;},has:function(value){return value in set;}};};}defineFallbacks();function fnv132(str){var hash=0x811C9DC5;for(var i=0;i<str.length;i++){hash+=(hash<<1)+(hash<<4)+(hash<<7)+(hash<<8)+(hash<<24);hash^=str.charCodeAt(i);}hash=(hash>>>0).toString(36).slice(0,5);while(hash.length<5){hash='0'+hash;}return hash;}var isES6Supported=typeof Promise==='function'&&Promise.
prototype.finally&&/./g.flags==='g'&&(function(){try{new Function('(a = 0) => a');return true;}catch(e){return false;}}());var registry=Object.create(null),sources=Object.create(null),handlingPendingRequests=false,pendingRequests=[],queue=[],jobs=[],willPropagate=false,errorModules=[],baseModules=["jquery","mediawiki.base"],marker=document.querySelector('meta[name="ResourceLoaderDynamicStyles"]'),lastCssBuffer,rAF=window.requestAnimationFrame||setTimeout;function addToHead(el,nextNode){if(nextNode&&nextNode.parentNode){nextNode.parentNode.insertBefore(el,nextNode);}else{document.head.appendChild(el);}}function newStyleTag(text,nextNode){var el=document.createElement('style');el.appendChild(document.createTextNode(text));addToHead(el,nextNode);return el;}function flushCssBuffer(cssBuffer){if(cssBuffer===lastCssBuffer){lastCssBuffer=null;}newStyleTag(cssBuffer.cssText,marker);for(var i=0;i<cssBuffer.callbacks.length;i++){cssBuffer.callbacks[i]();}}function addEmbeddedCSS(cssText,callback
){if(!lastCssBuffer||cssText.slice(0,7)==='@import'){lastCssBuffer={cssText:'',callbacks:[]};rAF(flushCssBuffer.bind(null,lastCssBuffer));}lastCssBuffer.cssText+='\n'+cssText;lastCssBuffer.callbacks.push(callback);}function getCombinedVersion(modules){var hashes=modules.reduce(function(result,module){return result+registry[module].version;},'');return fnv132(hashes);}function allReady(modules){for(var i=0;i<modules.length;i++){if(mw.loader.getState(modules[i])!=='ready'){return false;}}return true;}function allWithImplicitReady(module){return allReady(registry[module].dependencies)&&(baseModules.indexOf(module)!==-1||allReady(baseModules));}function anyFailed(modules){for(var i=0;i<modules.length;i++){var state=mw.loader.getState(modules[i]);if(state==='error'||state==='missing'){return modules[i];}}return false;}function doPropagation(){var didPropagate=true;var module;while(didPropagate){didPropagate=false;while(errorModules.length){var errorModule=errorModules.shift(),
baseModuleError=baseModules.indexOf(errorModule)!==-1;for(module in registry){if(registry[module].state!=='error'&&registry[module].state!=='missing'){if(baseModuleError&&baseModules.indexOf(module)===-1){registry[module].state='error';didPropagate=true;}else if(registry[module].dependencies.indexOf(errorModule)!==-1){registry[module].state='error';errorModules.push(module);didPropagate=true;}}}}for(module in registry){if(registry[module].state==='loaded'&&allWithImplicitReady(module)){execute(module);didPropagate=true;}}for(var i=0;i<jobs.length;i++){var job=jobs[i];var failed=anyFailed(job.dependencies);if(failed!==false||allReady(job.dependencies)){jobs.splice(i,1);i-=1;try{if(failed!==false&&job.error){job.error(new Error('Failed dependency: '+failed),job.dependencies);}else if(failed===false&&job.ready){job.ready();}}catch(e){mw.trackError('resourceloader.exception',{exception:e,source:'load-callback'});}didPropagate=true;}}}willPropagate=false;}function setAndPropagate(module,
state){registry[module].state=state;if(state==='ready'){store.add(module);}else if(state==='error'||state==='missing'){errorModules.push(module);}else if(state!=='loaded'){return;}if(willPropagate){return;}willPropagate=true;mw.requestIdleCallback(doPropagation,{timeout:1});}function sortDependencies(module,resolved,unresolved){if(!(module in registry)){throw new Error('Unknown module: '+module);}if(typeof registry[module].skip==='string'){var skip=(new Function(registry[module].skip)());registry[module].skip=!!skip;if(skip){registry[module].dependencies=[];setAndPropagate(module,'ready');return;}}if(!unresolved){unresolved=new StringSet();}var deps=registry[module].dependencies;unresolved.add(module);for(var i=0;i<deps.length;i++){if(resolved.indexOf(deps[i])===-1){if(unresolved.has(deps[i])){throw new Error('Circular reference detected: '+module+' -> '+deps[i]);}sortDependencies(deps[i],resolved,unresolved);}}resolved.push(module);}function resolve(modules){var resolved=baseModules.
slice();for(var i=0;i<modules.length;i++){sortDependencies(modules[i],resolved);}return resolved;}function resolveStubbornly(modules){var resolved=baseModules.slice();for(var i=0;i<modules.length;i++){var saved=resolved.slice();try{sortDependencies(modules[i],resolved);}catch(err){resolved=saved;mw.log.warn('Skipped unavailable module '+modules[i]);if(modules[i]in registry){mw.trackError('resourceloader.exception',{exception:err,source:'resolve'});}}}return resolved;}function resolveRelativePath(relativePath,basePath){var relParts=relativePath.match(/^((?:\.\.?\/)+)(.*)$/);if(!relParts){return null;}var baseDirParts=basePath.split('/');baseDirParts.pop();var prefixes=relParts[1].split('/');prefixes.pop();var prefix;while((prefix=prefixes.pop())!==undefined){if(prefix==='..'){baseDirParts.pop();}}return(baseDirParts.length?baseDirParts.join('/')+'/':'')+relParts[2];}function makeRequireFunction(moduleObj,basePath){return function require(moduleName){var fileName=resolveRelativePath(
moduleName,basePath);if(fileName===null){return mw.loader.require(moduleName);}if(hasOwn.call(moduleObj.packageExports,fileName)){return moduleObj.packageExports[fileName];}var scriptFiles=moduleObj.script.files;if(!hasOwn.call(scriptFiles,fileName)){throw new Error('Cannot require undefined file '+fileName);}var result,fileContent=scriptFiles[fileName];if(typeof fileContent==='function'){var moduleParam={exports:{}};fileContent(makeRequireFunction(moduleObj,fileName),moduleParam,moduleParam.exports);result=moduleParam.exports;}else{result=fileContent;}moduleObj.packageExports[fileName]=result;return result;};}function addScript(src,callback){var script=document.createElement('script');script.src=src;script.onload=script.onerror=function(){if(script.parentNode){script.parentNode.removeChild(script);}if(callback){callback();callback=null;}};document.head.appendChild(script);return script;}function queueModuleScript(src,moduleName,callback){pendingRequests.push(function(){if(moduleName
!=='jquery'){window.require=mw.loader.require;window.module=registry[moduleName].module;}addScript(src,function(){delete window.module;callback();if(pendingRequests[0]){pendingRequests.shift()();}else{handlingPendingRequests=false;}});});if(!handlingPendingRequests&&pendingRequests[0]){handlingPendingRequests=true;pendingRequests.shift()();}}function addLink(url,media,nextNode){var el=document.createElement('link');el.rel='stylesheet';if(media){el.media=media;}el.href=url;addToHead(el,nextNode);return el;}function domEval(code){var script=document.createElement('script');if(mw.config.get('wgCSPNonce')!==false){script.nonce=mw.config.get('wgCSPNonce');}script.text=code;document.head.appendChild(script);script.parentNode.removeChild(script);}function enqueue(dependencies,ready,error){if(allReady(dependencies)){if(ready){ready();}return;}var failed=anyFailed(dependencies);if(failed!==false){if(error){error(new Error('Dependency '+failed+' failed to load'),dependencies);}return;}if(ready||
error){jobs.push({dependencies:dependencies.filter(function(module){var state=registry[module].state;return state==='registered'||state==='loaded'||state==='loading'||state==='executing';}),ready:ready,error:error});}dependencies.forEach(function(module){if(registry[module].state==='registered'&&queue.indexOf(module)===-1){queue.push(module);}});mw.loader.work();}function execute(module){if(registry[module].state!=='loaded'){throw new Error('Module in state "'+registry[module].state+'" may not execute: '+module);}registry[module].state='executing';var runScript=function(){var script=registry[module].script;var markModuleReady=function(){setAndPropagate(module,'ready');};var nestedAddScript=function(arr,offset){if(offset>=arr.length){markModuleReady();return;}queueModuleScript(arr[offset],module,function(){nestedAddScript(arr,offset+1);});};try{if(Array.isArray(script)){nestedAddScript(script,0);}else if(typeof script==='function'){if(module==='jquery'){script();}else{script(window.$,
window.$,mw.loader.require,registry[module].module);}markModuleReady();}else if(typeof script==='object'&&script!==null){var mainScript=script.files[script.main];if(typeof mainScript!=='function'){throw new Error('Main file in module '+module+' must be a function');}mainScript(makeRequireFunction(registry[module],script.main),registry[module].module,registry[module].module.exports);markModuleReady();}else if(typeof script==='string'){domEval(script);markModuleReady();}else{markModuleReady();}}catch(e){setAndPropagate(module,'error');mw.trackError('resourceloader.exception',{exception:e,module:module,source:'module-execute'});}};if(registry[module].messages){mw.messages.set(registry[module].messages);}if(registry[module].templates){mw.templates.set(module,registry[module].templates);}var cssPending=0;var cssHandle=function(){cssPending++;return function(){cssPending--;if(cssPending===0){var runScriptCopy=runScript;runScript=undefined;runScriptCopy();}};};if(registry[module].style){for(
var key in registry[module].style){var value=registry[module].style[key];if(key==='css'){for(var i=0;i<value.length;i++){addEmbeddedCSS(value[i],cssHandle());}}else if(key==='url'){for(var media in value){var urls=value[media];for(var j=0;j<urls.length;j++){addLink(urls[j],media,marker);}}}}}if(module==='user'){var siteDeps;var siteDepErr;try{siteDeps=resolve(['site']);}catch(e){siteDepErr=e;runScript();}if(!siteDepErr){enqueue(siteDeps,runScript,runScript);}}else if(cssPending===0){runScript();}}function sortQuery(o){var sorted={};var list=[];for(var key in o){list.push(key);}list.sort();for(var i=0;i<list.length;i++){sorted[list[i]]=o[list[i]];}return sorted;}function buildModulesString(moduleMap){var str=[];var list=[];var p;function restore(suffix){return p+suffix;}for(var prefix in moduleMap){p=prefix===''?'':prefix+'.';str.push(p+moduleMap[prefix].join(','));list.push.apply(list,moduleMap[prefix].map(restore));}return{str:str.join('|'),list:list};}function makeQueryString(params)
{var str='';for(var key in params){str+=(str?'&':'')+encodeURIComponent(key)+'='+encodeURIComponent(params[key]);}return str;}function batchRequest(batch){if(!batch.length){return;}var sourceLoadScript,currReqBase,moduleMap;function doRequest(){var query=Object.create(currReqBase),packed=buildModulesString(moduleMap);query.modules=packed.str;query.version=getCombinedVersion(packed.list);query=sortQuery(query);addScript(sourceLoadScript+'?'+makeQueryString(query));}batch.sort();var reqBase={"lang":"en","skin":"timeless"};var splits=Object.create(null);for(var b=0;b<batch.length;b++){var bSource=registry[batch[b]].source;var bGroup=registry[batch[b]].group;if(!splits[bSource]){splits[bSource]=Object.create(null);}if(!splits[bSource][bGroup]){splits[bSource][bGroup]=[];}splits[bSource][bGroup].push(batch[b]);}for(var source in splits){sourceLoadScript=sources[source];for(var group in splits[source]){var modules=splits[source][group];currReqBase=Object.create(reqBase);if(group===0&&mw.
config.get('wgUserName')!==null){currReqBase.user=mw.config.get('wgUserName');}var currReqBaseLength=makeQueryString(currReqBase).length+23;var length=0;moduleMap=Object.create(null);for(var i=0;i<modules.length;i++){var lastDotIndex=modules[i].lastIndexOf('.'),prefix=modules[i].slice(0,Math.max(0,lastDotIndex)),suffix=modules[i].slice(lastDotIndex+1),bytesAdded=moduleMap[prefix]?suffix.length+3:modules[i].length+3;if(length&&length+currReqBaseLength+bytesAdded>mw.loader.maxQueryLength){doRequest();length=0;moduleMap=Object.create(null);}if(!moduleMap[prefix]){moduleMap[prefix]=[];}length+=bytesAdded;moduleMap[prefix].push(suffix);}doRequest();}}}function asyncEval(implementations,cb){if(!implementations.length){return;}mw.requestIdleCallback(function(){try{domEval(implementations.join(';'));}catch(err){cb(err);}});}function getModuleKey(module){return module in registry?(module+'@'+registry[module].version):null;}function splitModuleKey(key){var index=key.lastIndexOf('@');if(index===-
1||index===0){return{name:key,version:''};}return{name:key.slice(0,index),version:key.slice(index+1)};}function registerOne(module,version,dependencies,group,source,skip){if(module in registry){throw new Error('module already registered: '+module);}version=String(version||'');if(version.slice(-1)==='!'){if(!isES6Supported){return;}version=version.slice(0,-1);}registry[module]={module:{exports:{}},packageExports:{},version:version,dependencies:dependencies||[],group:typeof group==='undefined'?null:group,source:typeof source==='string'?source:'local',state:'registered',skip:typeof skip==='string'?skip:null};}mw.loader={moduleRegistry:registry,maxQueryLength:5000,addStyleTag:newStyleTag,addScriptTag:addScript,addLinkTag:addLink,enqueue:enqueue,resolve:resolve,work:function(){store.init();var q=queue.length,storedImplementations=[],storedNames=[],requestNames=[],batch=new StringSet();while(q--){var module=queue[q];if(mw.loader.getState(module)==='registered'&&!batch.has(module)){registry[
module].state='loading';batch.add(module);var implementation=store.get(module);if(implementation){storedImplementations.push(implementation);storedNames.push(module);}else{requestNames.push(module);}}}queue=[];asyncEval(storedImplementations,function(err){store.stats.failed++;store.clear();mw.trackError('resourceloader.exception',{exception:err,source:'store-eval'});var failed=storedNames.filter(function(name){return registry[name].state==='loading';});batchRequest(failed);});batchRequest(requestNames);},addSource:function(ids){for(var id in ids){if(id in sources){throw new Error('source already registered: '+id);}sources[id]=ids[id];}},register:function(modules){if(typeof modules!=='object'){registerOne.apply(null,arguments);return;}function resolveIndex(dep){return typeof dep==='number'?modules[dep][0]:dep;}for(var i=0;i<modules.length;i++){var deps=modules[i][2];if(deps){for(var j=0;j<deps.length;j++){deps[j]=resolveIndex(deps[j]);}}registerOne.apply(null,modules[i]);}},implement:
function(module,script,style,messages,templates){var split=splitModuleKey(module),name=split.name,version=split.version;if(!(name in registry)){mw.loader.register(name);}if(registry[name].script!==undefined){throw new Error('module already implemented: '+name);}if(version){registry[name].version=version;}registry[name].script=script||null;registry[name].style=style||null;registry[name].messages=messages||null;registry[name].templates=templates||null;if(registry[name].state!=='error'&&registry[name].state!=='missing'){setAndPropagate(name,'loaded');}},load:function(modules,type){if(typeof modules==='string'&&/^(https?:)?\/?\//.test(modules)){if(type==='text/css'){addLink(modules);}else if(type==='text/javascript'||type===undefined){addScript(modules);}else{throw new Error('Invalid type '+type);}}else{modules=typeof modules==='string'?[modules]:modules;enqueue(resolveStubbornly(modules));}},state:function(states){for(var module in states){if(!(module in registry)){mw.loader.register(
module);}setAndPropagate(module,states[module]);}},getState:function(module){return module in registry?registry[module].state:null;},require:function(moduleName){if(mw.loader.getState(moduleName)!=='ready'){throw new Error('Module "'+moduleName+'" is not loaded');}return registry[moduleName].module.exports;}};var hasPendingWrites=false;function flushWrites(){store.prune();while(store.queue.length){store.set(store.queue.shift());}try{localStorage.removeItem(store.key);var data=JSON.stringify(store);localStorage.setItem(store.key,data);}catch(e){mw.trackError('resourceloader.exception',{exception:e,source:'store-localstorage-update'});}hasPendingWrites=false;}mw.loader.store=store={enabled:null,items:{},queue:[],stats:{hits:0,misses:0,expired:0,failed:0},toJSON:function(){return{items:store.items,vary:store.vary,asOf:Math.ceil(Date.now()/1e7)};},key:"MediaWikiModuleStore:degreesofbizarrelewditymodwiki",vary:"timeless:1:en",init:function(){if(this.enabled===null){this.enabled=false;if(
false){this.load();}else{this.clear();}}},load:function(){try{var raw=localStorage.getItem(this.key);this.enabled=true;var data=JSON.parse(raw);if(data&&data.vary===this.vary&&data.items&&Date.now()<(data.asOf*1e7)+259e7){this.items=data.items;}}catch(e){}},get:function(module){if(this.enabled){var key=getModuleKey(module);if(key in this.items){this.stats.hits++;return this.items[key];}this.stats.misses++;}return false;},add:function(module){if(this.enabled){this.queue.push(module);this.requestUpdate();}},set:function(module){var args,encodedScript,descriptor=registry[module],key=getModuleKey(module);if(key in this.items||!descriptor||descriptor.state!=='ready'||!descriptor.version||descriptor.group===1||descriptor.group===0||[descriptor.script,descriptor.style,descriptor.messages,descriptor.templates].indexOf(undefined)!==-1){return;}try{if(typeof descriptor.script==='function'){encodedScript=String(descriptor.script);}else if(typeof descriptor.script==='object'&&descriptor.script&&!
Array.isArray(descriptor.script)){encodedScript='{'+'main:'+JSON.stringify(descriptor.script.main)+','+'files:{'+Object.keys(descriptor.script.files).map(function(file){var value=descriptor.script.files[file];return JSON.stringify(file)+':'+(typeof value==='function'?value:JSON.stringify(value));}).join(',')+'}}';}else{encodedScript=JSON.stringify(descriptor.script);}args=[JSON.stringify(key),encodedScript,JSON.stringify(descriptor.style),JSON.stringify(descriptor.messages),JSON.stringify(descriptor.templates)];}catch(e){mw.trackError('resourceloader.exception',{exception:e,source:'store-localstorage-json'});return;}var src='mw.loader.implement('+args.join(',')+');';if(src.length>1e5){return;}this.items[key]=src;},prune:function(){for(var key in this.items){if(getModuleKey(splitModuleKey(key).name)!==key){this.stats.expired++;delete this.items[key];}}},clear:function(){this.items={};try{localStorage.removeItem(this.key);}catch(e){}},requestUpdate:function(){if(!hasPendingWrites){
hasPendingWrites=true;setTimeout(function(){mw.requestIdleCallback(flushWrites);},2000);}}};}());mw.requestIdleCallbackInternal=function(callback){setTimeout(function(){var start=mw.now();callback({didTimeout:false,timeRemaining:function(){return Math.max(0,50-(mw.now()-start));}});},1);};mw.requestIdleCallback=window.requestIdleCallback?window.requestIdleCallback.bind(window):mw.requestIdleCallbackInternal;(function(){var queue;mw.loader.addSource({"local":"/w/load.php","metawiki":"//meta.miraheze.org/w/load.php"});mw.loader.register([["user.options","12s5i",[],1],["mediawiki.skinning.interface","km6yr"],["jquery.makeCollapsible.styles","qx5d5"],["mediawiki.skinning.content.parsoid","pvg6m"],["jquery","p9z7x"],["es6-polyfills","1xwex",[],null,null,"return Array.prototype.find\u0026\u0026Array.prototype.findIndex\u0026\u0026Array.prototype.includes\u0026\u0026typeof Promise==='function'\u0026\u0026Promise.prototype.finally;"],["web2017-polyfills","5cxhc",[5],null,null,
"return'IntersectionObserver'in window\u0026\u0026typeof fetch==='function'\u0026\u0026typeof URL==='function'\u0026\u0026'toJSON'in URL.prototype;"],["mediawiki.base","jwvid",[4]],["jquery.chosen","fjvzv"],["jquery.client","1jnox"],["jquery.color","1y5ur"],["jquery.confirmable","1qc1o",[104]],["jquery.cookie","emj1l"],["jquery.form","1djyv"],["jquery.fullscreen","1lanf"],["jquery.highlightText","a2wnf",[78]],["jquery.hoverIntent","1cahm"],["jquery.i18n","1pu0k",[103]],["jquery.lengthLimit","k5zgm",[62]],["jquery.makeCollapsible","1863g",[2,78]],["jquery.spinner","1rx3f",[21]],["jquery.spinner.styles","153wt"],["jquery.suggestions","1g6wh",[15]],["jquery.tablesorter","owtca",[24,105,78]],["jquery.tablesorter.styles","rwcx6"],["jquery.textSelection","m1do8",[9]],["jquery.throttle-debounce","1p2bq"],["jquery.tipsy","5uv8c"],["jquery.ui","6vx3o"],["moment","x1k6h",[101,78]],["vue","zfi8r!"],["@vue/composition-api","scw0q!",[30]],["vuex","1twvy!",[30]],["wvui","v4ef5!",[31]],["wvui-search"
,"1nhzn!",[30]],["@wikimedia/codex","r6zyv!",[30]],["@wikimedia/codex-search","1p7vn!",[30]],["mediawiki.template","bca94"],["mediawiki.template.mustache","199kg",[37]],["mediawiki.apipretty","19n2s"],["mediawiki.api","4z1te",[68,104]],["mediawiki.content.json","h3m91"],["mediawiki.confirmCloseWindow","1ewwa"],["mediawiki.debug","d8is9",[188]],["mediawiki.diff","paqy5"],["mediawiki.diff.styles","na4y2"],["mediawiki.feedback","looz4",[391,196]],["mediawiki.feedlink","1yq8n"],["mediawiki.filewarning","1brek",[188,200]],["mediawiki.ForeignApi","6vgsr",[381]],["mediawiki.ForeignApi.core","llzm2",[75,40,184]],["mediawiki.helplink","wjdrt"],["mediawiki.hlist","1eh1m"],["mediawiki.htmlform","1icg3",[18,78]],["mediawiki.htmlform.ooui","1m5pb",[188]],["mediawiki.htmlform.styles","1mdmd"],["mediawiki.htmlform.ooui.styles","t3imb"],["mediawiki.icon","17xpk"],["mediawiki.inspect","88qa7",[62,78]],["mediawiki.notification","1qg7p",[78,84]],["mediawiki.notification.convertmessagebox","1kd6x",[59]],[
"mediawiki.notification.convertmessagebox.styles","19vc0"],["mediawiki.String","1vc9s"],["mediawiki.pager.styles","eo2ge"],["mediawiki.pager.tablePager","1tupc"],["mediawiki.pulsatingdot","1i1zo"],["mediawiki.searchSuggest","18p6c",[22,40]],["mediawiki.storage","2gicm",[78]],["mediawiki.Title","1345o",[62,78]],["mediawiki.Upload","ooev2",[40]],["mediawiki.ForeignUpload","2bu58",[49,69]],["mediawiki.Upload.Dialog","198dv",[72]],["mediawiki.Upload.BookletLayout","54qr3",[69,76,29,191,196,201,202]],["mediawiki.ForeignStructuredUpload.BookletLayout","3n0xt",[70,72,108,167,161]],["mediawiki.toc","1jhap",[81]],["mediawiki.Uri","5izs0",[78]],["mediawiki.user","1fogn",[40,81]],["mediawiki.userSuggest","1hhzv",[22,40]],["mediawiki.util","1k0ph",[9,6]],["mediawiki.checkboxtoggle","159pl"],["mediawiki.checkboxtoggle.styles","1b0zv"],["mediawiki.cookie","tqeh8",[12]],["mediawiki.experiments","dhcyy"],["mediawiki.editfont.styles","12q5o"],["mediawiki.visibleTimeout","xcitq"],[
"mediawiki.action.delete","1ssul",[18,188]],["mediawiki.action.edit","mstk4",[25,87,40,83,163]],["mediawiki.action.edit.styles","1o953"],["mediawiki.action.edit.collapsibleFooter","za3yf",[19,57,67]],["mediawiki.action.edit.preview","1kz6y",[20,114,76]],["mediawiki.action.history","cpbx3",[19]],["mediawiki.action.history.styles","g8wz5"],["mediawiki.action.protect","1dt0w",[18,188]],["mediawiki.action.view.metadata","13p0w",[99]],["mediawiki.action.view.categoryPage.styles","acp5g"],["mediawiki.action.view.postEdit","13vzn",[104,59,188,207]],["mediawiki.action.view.redirect","iqcjx"],["mediawiki.action.view.redirectPage","khb3u"],["mediawiki.action.edit.editWarning","ihdqq",[25,42,104]],["mediawiki.action.view.filepage","mbna9"],["mediawiki.action.styles","g8x3w"],["mediawiki.language","1ysaw",[102]],["mediawiki.cldr","w8zqb",[103]],["mediawiki.libs.pluralruleparser","1kwne"],["mediawiki.jqueryMsg","w5jwt",[62,101,78,0]],["mediawiki.language.months","1iag2",[101]],[
"mediawiki.language.names","xk3kz",[101]],["mediawiki.language.specialCharacters","f8zox",[101]],["mediawiki.libs.jpegmeta","1h4oh"],["mediawiki.page.gallery","19ugl",[110,78]],["mediawiki.page.gallery.styles","16scj"],["mediawiki.page.gallery.slideshow","1f4yv",[40,191,210,212]],["mediawiki.page.ready","1toj5",[40]],["mediawiki.page.watch.ajax","45qm7",[40]],["mediawiki.page.preview","8a65o",[19,25,40,44,45,188]],["mediawiki.page.image.pagination","kn7b4",[20,78]],["mediawiki.rcfilters.filters.base.styles","k81tw"],["mediawiki.rcfilters.highlightCircles.seenunseen.styles","ce9wh"],["mediawiki.rcfilters.filters.ui","52vzp",[19,75,76,158,197,204,206,207,208,210,211]],["mediawiki.interface.helpers.styles","wdfed"],["mediawiki.special","1orsg"],["mediawiki.special.apisandbox","a9q18",[19,75,178,164,187]],["mediawiki.special.block","1n3h1",[53,161,177,168,178,175,204]],["mediawiki.misc-authed-ooui","1iw6h",[54,158,163]],["mediawiki.misc-authed-pref","16eja",[0]],[
"mediawiki.misc-authed-curate","1vp4k",[11,20,40]],["mediawiki.special.changeslist","19kr3"],["mediawiki.special.changeslist.watchlistexpiry","1tnj7",[120,207]],["mediawiki.special.changeslist.enhanced","1kflq"],["mediawiki.special.changeslist.legend","1b53v"],["mediawiki.special.changeslist.legend.js","qa88i",[19,81]],["mediawiki.special.contributions","1luqq",[19,104,161,187]],["mediawiki.special.edittags","79img",[8,18]],["mediawiki.special.import.styles.ooui","1hzv9"],["mediawiki.special.changecredentials","f9fqt"],["mediawiki.special.changeemail","10bxu"],["mediawiki.special.preferences.ooui","17q0e",[42,83,60,67,168,163]],["mediawiki.special.preferences.styles.ooui","11pyq"],["mediawiki.special.revisionDelete","13kw3",[18]],["mediawiki.special.search","11pp3",[180]],["mediawiki.special.search.commonsInterwikiWidget","e3z5z",[75,40]],["mediawiki.special.search.interwikiwidget.styles","cxv8q"],["mediawiki.special.search.styles","1murh"],["mediawiki.special.unwatchedPages","mk9s7",[
40]],["mediawiki.special.upload","1kaju",[20,40,42,108,120,37]],["mediawiki.special.userlogin.common.styles","1q3ah"],["mediawiki.special.userlogin.login.styles","1w9oo"],["mediawiki.special.createaccount","mbk5h",[40]],["mediawiki.special.userlogin.signup.styles","10luo"],["mediawiki.special.userrights","4k0n6",[18,60]],["mediawiki.special.watchlist","lr1n3",[40,188,207]],["mediawiki.ui","1qw5m"],["mediawiki.ui.checkbox","3rebp"],["mediawiki.ui.radio","lhqjo"],["mediawiki.ui.anchor","1yxgk"],["mediawiki.ui.button","19cke"],["mediawiki.ui.input","1lzvw"],["mediawiki.ui.icon","10ybi"],["mediawiki.widgets","1qekg",[40,159,191,201,202]],["mediawiki.widgets.styles","1x5du"],["mediawiki.widgets.AbandonEditDialog","1tcrg",[196]],["mediawiki.widgets.DateInputWidget","1axcu",[162,29,191,212]],["mediawiki.widgets.DateInputWidget.styles","15tly"],["mediawiki.widgets.visibleLengthLimit","m325n",[18,188]],["mediawiki.widgets.datetime","1m5jf",[78,188,207,211,212]],["mediawiki.widgets.expiry",
"m5uji",[164,29,191]],["mediawiki.widgets.CheckMatrixWidget","k9si1",[188]],["mediawiki.widgets.CategoryMultiselectWidget","x4tey",[49,191]],["mediawiki.widgets.SelectWithInputWidget","yzuek",[169,191]],["mediawiki.widgets.SelectWithInputWidget.styles","vkr7h"],["mediawiki.widgets.SizeFilterWidget","1hmr4",[171,191]],["mediawiki.widgets.SizeFilterWidget.styles","ceybj"],["mediawiki.widgets.MediaSearch","1y1s4",[49,76,191]],["mediawiki.widgets.Table","p2qhh",[191]],["mediawiki.widgets.TagMultiselectWidget","1erse",[191]],["mediawiki.widgets.UserInputWidget","jsk5k",[40,191]],["mediawiki.widgets.UsersMultiselectWidget","1m6vb",[40,191]],["mediawiki.widgets.NamespacesMultiselectWidget","pwj2l",[191]],["mediawiki.widgets.TitlesMultiselectWidget","gt95w",[158]],["mediawiki.widgets.TagMultiselectWidget.styles","1rjw4"],["mediawiki.widgets.SearchInputWidget","z70j2",[66,158,207]],["mediawiki.widgets.SearchInputWidget.styles","9327p"],["mediawiki.watchstar.widgets","1gkq3",[187]],[
"mediawiki.deflate","1ci7b"],["oojs","ewqeo"],["mediawiki.router","1ugrh",[186]],["oojs-router","m96yy",[184]],["oojs-ui","1jh3r",[194,191,196]],["oojs-ui-core","p1ebe",[101,184,190,189,198]],["oojs-ui-core.styles","lj92p"],["oojs-ui-core.icons","2cof4"],["oojs-ui-widgets","yjsdo",[188,193]],["oojs-ui-widgets.styles","13ehs"],["oojs-ui-widgets.icons","1r6fi"],["oojs-ui-toolbars","1ruxz",[188,195]],["oojs-ui-toolbars.icons","1igdr"],["oojs-ui-windows","8mo99",[188,197]],["oojs-ui-windows.icons","vibet"],["oojs-ui.styles.indicators","se837"],["oojs-ui.styles.icons-accessibility","flqp8"],["oojs-ui.styles.icons-alerts","1p1d2"],["oojs-ui.styles.icons-content","1ite7"],["oojs-ui.styles.icons-editing-advanced","1kd43"],["oojs-ui.styles.icons-editing-citation","v6xk6"],["oojs-ui.styles.icons-editing-core","c8o29"],["oojs-ui.styles.icons-editing-list","dbib0"],["oojs-ui.styles.icons-editing-styling","1d5lj"],["oojs-ui.styles.icons-interactions","azz0n"],["oojs-ui.styles.icons-layout","z5727"]
,["oojs-ui.styles.icons-location","1tnd8"],["oojs-ui.styles.icons-media","16iuo"],["oojs-ui.styles.icons-moderation","6axnx"],["oojs-ui.styles.icons-movement","17zgd"],["oojs-ui.styles.icons-user","1jpuf"],["oojs-ui.styles.icons-wikimedia","1ael5"],["skins.cologneblue","1xvc6"],["skins.modern","10ro9"],["skins.monobook.styles","1p1qa"],["skins.monobook.scripts","18gpk",[76,200]],["skins.timeless","1wqrc"],["skins.timeless.js","l49wn"],["skins.vector.search","tkov3!",[36,75]],["skins.vector.styles.legacy","oeaul"],["skins.vector.AB.styles","fdwf0"],["skins.vector.styles","friio"],["skins.vector.icons.js","bm64r"],["skins.vector.icons","3phva"],["skins.vector.es6","1xmtq!",[82,112,113,76,225]],["skins.vector.js","krfqj",[112,225]],["skins.vector.legacy.js","omaiv",[112]],["ext.abuseFilter","1y93l"],["ext.abuseFilter.edit","vtjdy",[20,25,40,42,191]],["ext.abuseFilter.tools","i65q3",[20,40]],["ext.abuseFilter.examine","pzrfk",[20,40]],["ext.abuseFilter.ace","1918f",["ext.codeEditor.ace"]],
["ext.abuseFilter.visualEditor","5wt0f"],["ext.betaFeatures","17jg5",[9,188]],["ext.betaFeatures.styles","1fvtf"],["ext.centralNotice.startUp","1tabw",[240]],["ext.centralNotice.geoIP","qgetp",[12]],["ext.centralNotice.choiceData","6gw17",[241]],["ext.centralNotice.display","11oqk",[239,242,279,75,67]],["ext.centralNotice.kvStore","39ge7"],["ext.centralNotice.bannerHistoryLogger","1qvnd",[241]],["ext.centralNotice.impressionDiet","w0vqa",[241]],["ext.centralNotice.largeBannerLimit","4hu05",[241]],["ext.centralNotice.legacySupport","sazar",[241]],["ext.centralNotice.bannerSequence","sy80g",[241]],["ext.centralNotice.freegeoipLookup","kefis",[239]],["ext.centralNotice.impressionEventsSampleRate","1601b",[241]],["ext.centralNotice.cspViolationAlert","1c5z9"],["ext.checkUser","189k5",[23,75,63,67,158,204,207,209,211,213]],["ext.checkUser.styles","14d8h"],["ext.guidedTour.tour.checkuserinvestigateform","1jrhm",["ext.guidedTour"]],["ext.guidedTour.tour.checkuserinvestigate","16oj9",[251,
"ext.guidedTour"]],["ext.createwiki.oouiform","9ehkb",[67,191]],["ext.createwiki.oouiform.styles","cx4qu"],["ext.CookieWarning","vni5h",[76]],["ext.CookieWarning.styles","1cd15"],["ext.CookieWarning.geolocation","1u82j",[257]],["ext.CookieWarning.geolocation.styles","15jk0"],["ext.confirmEdit.editPreview.ipwhitelist.styles","11y4q"],["ext.confirmEdit.visualEditor","rlq1b",[390]],["ext.confirmEdit.simpleCaptcha","14a9d"],["ext.confirmEdit.hCaptcha.visualEditor","4bjw3"],["ext.dismissableSiteNotice","1aopq",[12,78]],["ext.dismissableSiteNotice.styles","1udzj"],["ext.echo.logger","1eha4",[76,184]],["ext.echo.ui.desktop","15tiu",[274,269]],["ext.echo.ui","sg53r",[270,267,389,191,200,201,207,211,212,213]],["ext.echo.dm","1n4ej",[273,29]],["ext.echo.api","14pf5",[49]],["ext.echo.mobile","10pkb",[269,185,38]],["ext.echo.init","1kmqf",[271]],["ext.echo.styles.badge","13mib"],["ext.echo.styles.notifications","6q585"],["ext.echo.styles.alert","7jmh0"],["ext.echo.special","wybrg",[278,269]],[
"ext.echo.styles.special","1uy63"],["ext.eventLogging","1ax2d",[76]],["ext.eventLogging.debug","2c69a"],["ext.eventLogging.jsonSchema","1byf3"],["ext.eventLogging.jsonSchema.styles","127yh"],["ext.importdump.oouiform","mjsa2",[67,191]],["ext.importdump.oouiform.styles","k74dy"],["ext.incidentreporting.oouiform","yw10a",[67,191]],["ext.incidentreporting.oouiform.styles","12w9s"],["ext.interwiki.specialpage","lsm82"],["ext.ipInfo","1miix",[40,53,67,191,201]],["ext.ipInfo.styles","8zsqy"],["ext.managewiki.oouiform","6f9eu",[42,67,178,196]],["ext.managewiki.oouiform.styles","1f1io"],["ext.matomoanalytics.oouiform","f2fy4",[67,191]],["ext.matomoanalytics.oouiform.styles","1rkqq"],["ext.MobileDetect.mobileonly","11a4a"],["ext.MobileDetect.nomobile","a48si"],["ext.nuke.confirm","14ono",[104]],["ext.oath.totp.showqrcode","vp9jv"],["ext.oath.totp.showqrcode.styles","16j3z"],["ext.MWOAuth.styles","nxwda"],["ext.MWOAuth.AuthorizeDialog","mlroo",[196]],["ext.scribunto.errors","s78x0",[28]],[
"ext.scribunto.logs","c053i"],["ext.scribunto.edit","pr9mn",[20,40]],["ext.spamBlacklist.visualEditor","xlus7"],["mediawiki.api.titleblacklist","63y45",[40]],["ext.titleblacklist.visualEditor","105v1"],["ext.webauthn.ui.base","w8f77",[104,187]],["ext.webauthn.register","1rsrh",[307,40]],["ext.webauthn.login","sxone",[307]],["ext.webauthn.manage","xgabl",[307,40]],["ext.webauthn.disable","13dsk",[307]],["ext.wikiEditor","1fena",[25,28,107,76,158,203,204,205,206,210,37],2],["ext.wikiEditor.styles","1exfq",[],2],["ext.wikiEditor.images","13bcj"],["ext.wikiEditor.realtimepreview","1w5xs",[312,314,114,65,67,207]],["ext.categoryTree","1j302",[40]],["ext.categoryTree.styles","1d80w"],["ext.cite.styles","1o8is"],["ext.cite.style","6t36z"],["ext.cite.visualEditor.core","4m7e0",["ext.visualEditor.mwcore","ext.visualEditor.mwtransclusion"]],["ext.cite.visualEditor","s3t01",[319,318,320,"ext.visualEditor.base","ext.visualEditor.mediawiki",200,203,207]],["ext.cite.ux-enhancements","14f0k"],[
"ext.pygments","3yewq"],["ext.pygments.linenumbers","1ra7j",[78]],["ext.geshi.visualEditor","16uth",["ext.visualEditor.mwcore",202]],["ext.citeThisPage","zt3yx"],["ext.urlShortener.special","1c2go",[75,54,158,187]],["ext.urlShortener.toolbar","fhfzu",[40]],["ext.DarkMode","1dflm!",[199]],["ext.DarkMode.styles","1def3"],["ext.GlobalUserPage","1iy5x"],["mobile.pagelist.styles","5csrr"],["mobile.pagesummary.styles","11wvt"],["mobile.placeholder.images","1bxmy"],["mobile.userpage.styles","1x51l"],["mobile.startup.images","1frqq"],["mobile.init.styles","y6oqe"],["mobile.init","17nsn",[75,341]],["mobile.ooui.icons","b190n"],["mobile.user.icons","1sqxd"],["mobile.startup","1xvmz",[113,185,67,38,155,157,76,339,332,333,334,336]],["mobile.editor.overlay","dldqp",[42,83,59,156,160,343,341,340,187,204]],["mobile.editor.images","hfqip"],["mobile.talk.overlays","kpeqd",[154,342]],["mobile.mediaViewer","1c7mf",[341]],["mobile.languages.structured","qzddi",[341]],["mobile.special.mobileoptions.styles"
,"13b00"],["mobile.special.mobileoptions.scripts","12rxl",[341]],["mobile.special.nearby.styles","1xr51"],["mobile.special.userlogin.scripts","19ke0"],["mobile.special.nearby.scripts","15fmh",[75,349,341]],["mobile.special.mobilediff.images","1jyjr"],["ext.purge","mv3nq"],["skins.minerva.base.styles","1w006"],["skins.minerva.content.styles.images","15sur"],["skins.minerva.icons.loggedin","gro09"],["skins.minerva.amc.styles","bf5y6"],["skins.minerva.overflow.icons","1c7ux"],["skins.minerva.icons.wikimedia","dbtfl"],["skins.minerva.icons.images.scripts.misc","1g2yv"],["skins.minerva.icons.page.issues.uncolored","faem8"],["skins.minerva.icons.page.issues.default.color","14cnu"],["skins.minerva.icons.page.issues.medium.color","wbhz9"],["skins.minerva.mainPage.styles","1em1d"],["skins.minerva.userpage.styles","19xse"],["skins.minerva.talk.styles","5gxxp"],["skins.minerva.personalMenu.icons","1dw5c"],["skins.minerva.mainMenu.advanced.icons","ch4rz"],["skins.minerva.mainMenu.icons","u7toc"],[
"skins.minerva.mainMenu.styles","1z108"],["skins.minerva.loggedin.styles","1bz3m"],["skins.minerva.scripts","1afzq",[75,82,154,341,360,362,363,361,369,370,373]],["skins.minerva.messageBox.styles","19ljc"],["skins.minerva.categories.styles","7wnuj"],["ext.centralauth","1dw37",[20,78]],["ext.centralauth.centralautologin","b8i7z",[104]],["ext.centralauth.centralautologin.clearcookie","1vnks"],["ext.centralauth.misc.styles","izsxu"],["ext.centralauth.globaluserautocomplete","1klp1",[22,40]],["ext.centralauth.globalrenameuser","14r8a",[78]],["ext.centralauth.ForeignApi","ec996",[50]],["ext.widgets.GlobalUserInputWidget","jttb0",[40,191]],["ext.GlobalPreferences.global","1gcbg",[158,166,176]],["ext.GlobalPreferences.global-nojs","pvuhn"],["ext.GlobalPreferences.local-nojs","fl6qz"],["ext.centralauth.globalrenamequeue","1mief"],["ext.centralauth.globalrenamequeue.styles","rizt8"],["ext.echo.emailicons","1elo9"],["ext.echo.secondaryicons","ifxx4"],["ext.confirmEdit.CaptchaInputWidget","15usq",
[188]],["mediawiki.messagePoster","13b1w",[49]]]);mw.config.set(window.RLCONF||{});mw.loader.state(window.RLSTATE||{});mw.loader.load(window.RLPAGEMODULES||[]);queue=window.RLQ||[];RLQ=[];RLQ.push=function(fn){if(typeof fn==='function'){fn();}else{RLQ[RLQ.length]=fn;}};while(queue[0]){RLQ.push(queue.shift());}NORLQ={push:function(){}};}());}