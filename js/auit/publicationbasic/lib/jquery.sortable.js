(function(c){var b,a=c();c.fn.sortable=function(d){var e=String(d);d=c.extend({connectWith:false},d);return this.each(function(){if(/^enable|disable|destroy$/.test(e)){var f=c(this).children(c(this).data("items")).attr("draggable",e=="enable");if(e=="destroy"){f.add(this).removeData("connectWith items").off("dragstart.h5s dragend.h5s selectstart.h5s dragover.h5s dragenter.h5s drop.h5s")}return}var h,g,f=c(this).children(d.items);var i=c("<"+(/^ul|ol$/i.test(this.tagName)?"li":"div")+' class="sortable-placeholder">');f.find(d.handle).mousedown(function(){h=true}).mouseup(function(){h=false});c(this).data("items",d.items);a=a.add(i);if(d.connectWith){c(d.connectWith).add(this).data("connectWith",d.connectWith)}f.attr("draggable","true").on("dragstart.h5s",function(k){if(d.handle&&!h){return false}h=false;var j=k.originalEvent.dataTransfer;j.effectAllowed="move";j.setData("Text","dummy");g=(b=c(this)).addClass("sortable-dragging").index()}).on("dragend.h5s",function(){if(!b){return}b.removeClass("sortable-dragging").show();a.detach();if(g!=b.index()){b.parent().trigger("sortupdate",{item:b})}b=null}).not("a[href], img").on("selectstart.h5s",function(){this.dragDrop&&this.dragDrop();return false}).end().add([this,i]).on("dragover.h5s dragenter.h5s drop.h5s",function(j){if(!f.is(b)&&d.connectWith!==c(b).parent().data("connectWith")){return true}if(j.type=="drop"){j.stopPropagation();a.filter(":visible").after(b);b.trigger("dragend.h5s");return false}j.preventDefault();j.originalEvent.dataTransfer.dropEffect="move";if(f.is(this)){if(d.forcePlaceholderSize){i.height(b.outerHeight())}b.hide();c(this)[i.index()<c(this).index()?"after":"before"](i);a.not(i).detach()}else{if(!a.is(this)&&!c(this).children(d.items).length){a.detach();c(this).append(i)}}return false})})}})(jQuery);