/*!
 * Copyright (c) 2014 Brian Chan (bchanx.com)
 * All Rights Reserved.
 */

var bchanx=bchanx||{};
$(function(){var e=function(a,b){if(Array.prototype.indexOf)return a.indexOf(b);if(a&&a.length)for(var c=0,d=a.length;c<d;c++)if(a[c]==b)return c;return-1},s=["one","two","three","four","one"],t=["six","five","four","six"],m=function(a,b){b=b||"linear";a.add("h",s,b,!0).add("v",t,b,!0).start();var c=b;$("#slidr-home-demo-effects > div").removeClass("active");$("#slidr-home-demo-effects > div."+c).addClass("active");$("#slidr-home-demo > div").text(c)},c=slidr.create("slidr-home-demo",{overflow:!0,
keyboard:!0});m(c,"linear");c.auto();var d="ontouchstart"in window?"touchend":"click";$('aside[id="slidr-home-demo-control"]').one(d,function(){c.stop()});$(document).one("keydown",function(){c.stop()});$('aside[id="slidr-home-demo-breadcrumbs"]').one(d,function(){c.stop()});$("#slidr-home-demo-effects > div").each(function(){$(this).bind(d,function(){m(c,$(this).text())})});var f="border",h=["border","corner","none"];$("#slidr-home-demo-settings > div").each(function(){$(this).bind(d,function(){var a=
$(this).text();"controls"===a?(f=h[(e(h,f)+1)%h.length],c.controls(f),"none"===f?$(this).removeClass("active"):$(this).addClass("active")):"breadcrumbs"===a&&("hidden"===$("#slidr-home-demo-breadcrumbs").css("visibility")?$(this).addClass("active"):$(this).removeClass("active"),c.breadcrumbs())})});if(bchanx.isIE)$(".slidr-docs").remove(),$('a[href="#docs"]').attr("href","https://github.com/bchanx/slidr");else{$(".markdown").each(function(){this.innerHTML=marked("".trim?this.innerHTML.trim():this.innerHTML.replace(/^\s+|\s+$/g,
""))});hljs.initHighlightingOnLoad();slidr.create("slidr-nav-demo",{controls:"none",overflow:!0,keyboard:!0,touch:!0}).add("h",["one","two","three","one"],"cube").add("v",["one","two","three","one"],"linear").start();for(var n=[slidr.create("slidr-div",{theme:"#222"}).start(),slidr.create("slidr-img",{theme:"#222"}).start(),slidr.create("slidr-ul",{theme:"#222"}).start()],g=["#slidr-div-control","#slidr-img-control","#slidr-ul-control"],p=0,k;k=g[p];p++)$(k+" .slidr-control.left").bind(d,function(a){a.preventDefault?
a.preventDefault():a.returnValue=!1;a.stopPropagation();a=0;for(var b;b=n[a];a++)b.slide("left")}),$(k+" .slidr-control.right").bind(d,function(a){a.preventDefault?a.preventDefault():a.returnValue=!1;a.stopPropagation();a=0;for(var b;b=n[a];a++)b.slide("right")});slidr.create("slidr-api-demo",{breadcrumbs:!0,overflow:!0}).add("h",["one","two","three","one"]).add("v",["five","four","three","five"],"cube").start();slidr.create("slidr-css-demo",{breadcrumbs:!0,overflow:!0,transition:"cube"}).add("h",
["one","two","three","one"],"linear").start();slidr.create("slidr-inline-dynamic",{transition:"cube",controls:"none"}).add("v",["one","three","two","one"]).auto(3E3,"up");slidr.create("slidr-inline-static",{transition:"cube",controls:"none"}).add("v",["one","three","two","one"]).auto(3E3,"up");var l=[];$(".markdown[id]").each(function(){var a=$(this).find("h2").get(0);if(a){var b=$('<div class="breadcrumb-link"><div class="action"><a href="#'+a.innerHTML.toLowerCase()+'">'+a.innerHTML+'</a></div><div class="top"><a href="#">Top</a></div><h2>'+
a.innerHTML+"</h2></div>").get(0);a.parentNode.insertBefore(b,a);a.parentNode.removeChild(a);l.push("#"+a.innerHTML.toLowerCase())}})}var u=["#home","#docs"],q=function(){var a=window.location.hash;return 0<=e(u,a)?a.slice(1):0<=e(l,a)?"docs":""===a&&0>e(window.location.href,"#")?"home":null},r=function(){var a=window.location.hash,b=$('a[href="'+a+'"]');0<=e(l,a)&&b&&b.get(0).click()},g=q(),v=slidr.create("slidr",{controls:"none",transition:"cube",overflow:!0}).start(g);"docs"===g&&r();$(window).bind("hashchange",
function(a){if(a=q())v.slide(a),r()})});