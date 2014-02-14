!function(a){a.widget("mjs.nestedSortable",a.extend({},a.ui.sortable.prototype,{options:{tabSize:20,disableNesting:"mjs-nestedSortable-no-nesting",errorClass:"mjs-nestedSortable-error",listType:"ol",maxLevels:0,protectRoot:!1,rootID:null,rtl:!1,isAllowed:function(){return!0}},_create:function(){if(this.element.data("sortable",this.element.data("nestedSortable")),!this.element.is(this.options.listType))throw new Error("nestedSortable: Please check the listType option is set to your actual list type");return a.ui.sortable.prototype._create.apply(this,arguments)},destroy:function(){return this.element.removeData("nestedSortable").unbind(".nestedSortable"),a.ui.sortable.prototype.destroy.apply(this,arguments)},_mouseDrag:function(b){if(this.position=this._generatePosition(b),this.positionAbs=this._convertPositionTo("absolute"),this.lastPositionAbs||(this.lastPositionAbs=this.positionAbs),this.options.scroll){var c=this.options,d=!1;this.scrollParent[0]!=document&&"HTML"!=this.scrollParent[0].tagName?(this.overflowOffset.top+this.scrollParent[0].offsetHeight-b.pageY<c.scrollSensitivity?this.scrollParent[0].scrollTop=d=this.scrollParent[0].scrollTop+c.scrollSpeed:b.pageY-this.overflowOffset.top<c.scrollSensitivity&&(this.scrollParent[0].scrollTop=d=this.scrollParent[0].scrollTop-c.scrollSpeed),this.overflowOffset.left+this.scrollParent[0].offsetWidth-b.pageX<c.scrollSensitivity?this.scrollParent[0].scrollLeft=d=this.scrollParent[0].scrollLeft+c.scrollSpeed:b.pageX-this.overflowOffset.left<c.scrollSensitivity&&(this.scrollParent[0].scrollLeft=d=this.scrollParent[0].scrollLeft-c.scrollSpeed)):(b.pageY-a(document).scrollTop()<c.scrollSensitivity?d=a(document).scrollTop(a(document).scrollTop()-c.scrollSpeed):a(window).height()-(b.pageY-a(document).scrollTop())<c.scrollSensitivity&&(d=a(document).scrollTop(a(document).scrollTop()+c.scrollSpeed)),b.pageX-a(document).scrollLeft()<c.scrollSensitivity?d=a(document).scrollLeft(a(document).scrollLeft()-c.scrollSpeed):a(window).width()-(b.pageX-a(document).scrollLeft())<c.scrollSensitivity&&(d=a(document).scrollLeft(a(document).scrollLeft()+c.scrollSpeed))),d!==!1&&a.ui.ddmanager&&!c.dropBehaviour&&a.ui.ddmanager.prepareOffsets(this,b)}this.positionAbs=this._convertPositionTo("absolute"),this.options.axis&&"y"==this.options.axis||(this.helper[0].style.left=this.position.left+"px"),this.options.axis&&"x"==this.options.axis||(this.helper[0].style.top=this.position.top+"px");for(var e=this.items.length-1;e>=0;e--){var f=this.items[e],g=f.item[0],h=this._intersectsWithPointer(f);if(h&&g!=this.currentItem[0]&&this.placeholder[1==h?"next":"prev"]()[0]!=g&&!a.contains(this.placeholder[0],g)&&("semi-dynamic"==this.options.type?!a.contains(this.element[0],g):!0)){if(a(g).mouseenter(),this.direction=1==h?"down":"up","pointer"!=this.options.tolerance&&!this._intersectsWithSides(f))break;a(g).mouseleave(),this._rearrange(b,f),this._clearEmpty(g),this._trigger("change",b,this._uiHash());break}}var i=this.placeholder[0].parentNode.parentNode&&a(this.placeholder[0].parentNode.parentNode).closest(".ui-sortable").length?a(this.placeholder[0].parentNode.parentNode):null,j=this._getLevel(this.placeholder),k=this._getChildLevels(this.helper),l=this.placeholder[0].previousSibling?a(this.placeholder[0].previousSibling):null;if(null!=l)for(;"li"!=l[0].nodeName.toLowerCase()||l[0]==this.currentItem[0];){if(!l[0].previousSibling){l=null;break}l=a(l[0].previousSibling)}var m=document.createElement(c.listType);return this.beyondMaxLevels=0,null!=i&&(c.rtl&&this.positionAbs.left+this.helper.outerWidth()>i.offset().left+i.outerWidth()||!c.rtl&&this.positionAbs.left<i.offset().left)?(i.after(this.placeholder[0]),this._clearEmpty(i[0]),this._trigger("change",b,this._uiHash())):null!=l&&(c.rtl&&this.positionAbs.left+this.helper.outerWidth()<l.offset().left+l.outerWidth()-c.tabSize||!c.rtl&&this.positionAbs.left>l.offset().left+c.tabSize)?(this._isAllowed(l,j,j+k+1),l.children(c.listType).length||l[0].appendChild(m),l.children(c.listType)[0].appendChild(this.placeholder[0]),this._trigger("change",b,this._uiHash())):this._isAllowed(i,j,j+k),this._contactContainers(b),a.ui.ddmanager&&a.ui.ddmanager.drag(this,b),this._trigger("sort",b,this._uiHash()),this.lastPositionAbs=this.positionAbs,!1},_mouseStop:function(b){this.beyondMaxLevels&&(this.placeholder.removeClass(this.options.errorClass),this.domPosition.prev?a(this.domPosition.prev).after(this.placeholder):a(this.domPosition.parent).prepend(this.placeholder),this._trigger("revert",b,this._uiHash()));for(var c=this.items.length-1;c>=0;c--){var d=this.items[c].item[0];this._clearEmpty(d)}a.ui.sortable.prototype._mouseStop.apply(this,arguments)},serialize:function(b){var c=a.extend({},this.options,b),d=this._getItemsAsjQuery(c&&c.connected),e=[];return a(d).each(function(){var b=(a(c.item||this).attr(c.attribute||"id")||"").match(c.expression||/(.+)[-=_](.+)/),d=(a(c.item||this).parent(c.listType).parent(c.items).attr(c.attribute||"id")||"").match(c.expression||/(.+)[-=_](.+)/);b&&e.push((c.key||b[1])+"["+(c.key&&c.expression?b[1]:b[2])+"]="+(d?c.key&&c.expression?d[1]:d[2]:c.rootID))}),!e.length&&c.key&&e.push(c.key+"="),e.join("&")},toHierarchy:function(b){function c(b){var e=(a(b).attr(d.attribute||"id")||"").match(d.expression||/(.+)[-=_](.+)/);if(e){var f={id:e[2]};return a(b).children(d.listType).children(d.items).length>0&&(f.children=[],a(b).children(d.listType).children(d.items).each(function(){var a=c(this);f.children.push(a)})),f}}var d=a.extend({},this.options,b),e=(d.startDepthCount||0,[]);return a(this.element).children(d.items).each(function(){var a=c(this);e.push(a)}),e},toArray:function(b){function c(b,g,h){var i,j,k=h+1;if(a(b).children(d.listType).children(d.items).length>0&&(g++,a(b).children(d.listType).children(d.items).each(function(){k=c(a(this),g,k)}),g--),i=a(b).attr(d.attribute||"id").match(d.expression||/(.+)[-=_](.+)/),g===e+1)j=d.rootID;else{var l=a(b).parent(d.listType).parent(d.items).attr(d.attribute||"id").match(d.expression||/(.+)[-=_](.+)/);j=l[2]}return i&&f.push({item_id:i[2],parent_id:j,depth:g,left:h,right:k}),h=k+1}var d=a.extend({},this.options,b),e=d.startDepthCount||0,f=[],g=2;return f.push({item_id:d.rootID,parent_id:"none",depth:e,left:"1",right:2*(a(d.items,this.element).length+1)}),a(this.element).children(d.items).each(function(){g=c(this,e+1,g)}),f=f.sort(function(a,b){return a.left-b.left})},_clearEmpty:function(b){var c=a(b).children(this.options.listType);c.length&&!c.children().length&&c.remove()},_getLevel:function(a){var b=1;if(this.options.listType)for(var c=a.closest(this.options.listType);!c.is(".ui-sortable");)b++,c=c.parent().closest(this.options.listType);return b},_getChildLevels:function(b,c){var d=this,e=this.options,f=0;return c=c||0,a(b).children(e.listType).children(e.items).each(function(a,b){f=Math.max(d._getChildLevels(b,c+1),f)}),c?f+1:f},_isAllowed:function(b,c,d){var e=this.options,f=a(this.domPosition.parent).hasClass("ui-sortable")?!0:!1;!e.isAllowed(b,this.placeholder)||b&&b.hasClass(e.disableNesting)||e.protectRoot&&(null==b&&!f||f&&c>1)?(this.placeholder.addClass(e.errorClass),this.beyondMaxLevels=e.maxLevels<d&&0!=e.maxLevels?d-e.maxLevels:1):e.maxLevels<d&&0!=e.maxLevels?(this.placeholder.addClass(e.errorClass),this.beyondMaxLevels=d-e.maxLevels):(this.placeholder.removeClass(e.errorClass),this.beyondMaxLevels=0)}})),a.mjs.nestedSortable.prototype.options=a.extend({},a.ui.sortable.prototype.options,a.mjs.nestedSortable.prototype.options)}(jQuery);
//# sourceMappingURL=jquery.ui.nestedSortable.map