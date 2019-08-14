/* gridster.js - v0.1.0 - 2012-08-05
* http://gridster.net/
* Copyright (c) 2012 ducksboard; Licensed MIT */
(function(e,d,a,f){function b(g){if(g[0]&&e.isPlainObject(g[0])){this.data=g[0]}else{this.el=g}this.isCoords=true;this.coords={};this.init();return this}var c=b.prototype;c.init=function(){this.set();this.original_coords=this.get()};c.set=function(k,g){var h=this.el;if(h&&!k){this.data=h.offset();this.data.width=h.width();this.data.height=h.height()}if(h&&k&&!g){var j=h.offset();this.data.top=j.top;this.data.left=j.left}var i=this.data;this.coords.x1=i.left;this.coords.y1=i.top;this.coords.x2=i.left+i.width;this.coords.y2=i.top+i.height;this.coords.cx=i.left+(i.width/2);this.coords.cy=i.top+(i.height/2);this.coords.width=i.width;this.coords.height=i.height;this.coords.el=h||false;return this};c.update=function(h){if(!h&&!this.el){return this}if(h){var g=e.extend({},this.data,h);this.data=g;return this.set(true,true)}this.set(true);return this};c.get=function(){return this.coords};e.fn.coords=function(){if(this.data("coords")){return this.data("coords")}var g=new b(this,arguments[0]);this.data("coords",g);return g}}(jQuery,window,document));(function(d,c,a,f){var e={colliders_context:a.body};function g(i,j,h){this.options=d.extend(e,h);this.$element=i;this.last_colliders=[];this.last_colliders_coords=[];if(typeof j==="string"||j instanceof jQuery){this.$colliders=d(j,this.options.colliders_context).not(this.$element)}else{this.colliders=d(j)}this.init()}var b=g.prototype;b.init=function(){this.find_collisions()};b.overlaps=function(j,i){var h=false;var k=false;if((i.x1>=j.x1&&i.x1<=j.x2)||(i.x2>=j.x1&&i.x2<=j.x2)||(j.x1>=i.x1&&j.x2<=i.x2)){h=true}if((i.y1>=j.y1&&i.y1<=j.y2)||(i.y2>=j.y1&&i.y2<=j.y2)||(j.y1>=i.y1&&j.y2<=i.y2)){k=true}return(h&&k)};b.detect_overlapping_region=function(i,h){var k="";var j="";if(i.y1>h.cy&&i.y1<h.y2){k="N"}if(i.y2>h.y1&&i.y2<h.cy){k="S"}if(i.x1>h.cx&&i.x1<h.x2){j="W"}if(i.x2>h.x1&&i.x2<h.cx){j="E"}return(k+j)||"C"};b.calculate_overlapped_area_coords=function(i,h){var k=Math.max(i.x1,h.x1);var m=Math.max(i.y1,h.y1);var j=Math.min(i.x2,h.x2);var l=Math.min(i.y2,h.y2);return d({left:k,top:m,width:(j-k),height:(l-m)}).coords().get()};b.calculate_overlapped_area=function(h){return(h.width*h.height)};b.manage_colliders_start_stop=function(p,n,q){var o=this.last_colliders_coords;for(var m=0,h=o.length;m<h;m++){if(d.inArray(o[m],p)===-1){n.call(this,o[m])}}for(var k=0,l=p.length;k<l;k++){if(d.inArray(p[k],o)===-1){q.call(this,p[k])}}};b.find_collisions=function(r){var t=this;var l=[];var m=[];var o=(this.colliders||this.$colliders);var p=o.length;var j=t.$element.coords().update(r||false).get();while(p--){var h=t.$colliders?d(o[p]):o[p];var v=(h.isCoords)?h:h.coords();var u=v.get();var k=t.overlaps(j,u);if(!k){continue}var q=t.detect_overlapping_region(j,u);if(q==="C"){var s=t.calculate_overlapped_area_coords(j,u);var i=t.calculate_overlapped_area(s);var n={area:i,area_coords:s,region:q,coords:u,player_coords:j,el:h};if(t.options.on_overlap){t.options.on_overlap.call(this,n)}l.push(v);m.push(n)}}if(t.options.on_overlap_stop||t.options.on_overlap_start){this.manage_colliders_start_stop(l,t.options.on_overlap_stop,t.options.on_overlap_start)}this.last_colliders_coords=l;return m};b.get_closest_colliders=function(h){var i=this.find_collisions(h);var j=100;i.sort(function(l,k){if(l.area<=j){return 1}if(l.region==="C"&&k.region==="C"){if(l.coords.y1<k.coords.y1||l.coords.x1<k.coords.x1){return -1}else{return 1}}if(l.area<k.area){return 1}return 1});return i};d.fn.collision=function(i,h){return new g(this,i,h)}}(jQuery,window,document));(function(a,b){a.debounce=function(d,f,c){var e;return function(){var i=this,h=arguments;var g=function(){e=null;if(!c){d.apply(i,h)}};if(c&&!e){d.apply(i,h)}clearTimeout(e);e=setTimeout(g,f)}};a.throttle=function(e,f){var d,h,i,j,g,k;var c=debounce(function(){g=j=false},f);return function(){d=this;h=arguments;var l=function(){i=null;if(g){e.apply(d,h)}c()};if(!i){i=setTimeout(l,f)}if(j){g=true}else{k=e.apply(d,h)}c();j=true;return k}}})(window);(function(f,h,i,c){var d={items:".gs_w",distance:1,limit:true,offset_left:0,autoscroll:true};var b=f(h);var e=!!("ontouchstart" in h);var g={start:e?"touchstart":"mousedown.draggable",move:e?"touchmove":"mousemove.draggable",end:e?"touchend":"mouseup.draggable"};function a(l,k){this.options=f.extend({},d,k);this.$body=f(i.body);this.$container=f(l);this.$dragitems=f(this.options.items,this.$container);this.is_dragging=false;this.player_min_left=0+this.options.offset_left;this.init()}var j=a.prototype;j.init=function(){this.calculate_positions();this.$container.css("position","relative");this.enable();f(h).bind("resize",throttle(f.proxy(this.calculate_positions,this),200))};j.get_actual_pos=function(k){var l=k.position();return l};j.get_mouse_pos=function(k){if(e){var l=k.originalEvent;k=l.touches.length?l.touches[0]:l.changedTouches[0]}return{left:k.clientX,top:k.clientY}};j.get_offset=function(p){p.preventDefault();var k=this.get_mouse_pos(p);var o=Math.round(k.left-this.mouse_init_pos.left);var l=Math.round(k.top-this.mouse_init_pos.top);var n=Math.round(this.el_init_offset.left+o-this.baseX);var m=Math.round(this.el_init_offset.top+l-this.baseY+this.scrollOffset);if(this.options.limit){if(n>this.player_max_left){n=this.player_max_left}else{if(n<this.player_min_left){n=this.player_min_left}}}return{left:n,top:m,mouse_left:k.left,mouse_top:k.top}};j.manage_scroll=function(o){var t;var m=b.scrollTop();var s=m;var r=s+this.window_height;var l=r-50;var k=s+50;var p=o.mouse_left;var q=s+o.mouse_top;var n=(this.doc_height-this.window_height+this.player_height);if(q>=l){t=m+30;if(t<n){b.scrollTop(t);this.scrollOffset=this.scrollOffset+30}}if(q<=k){t=m-30;if(t>0){b.scrollTop(t);this.scrollOffset=this.scrollOffset-30}}};j.calculate_positions=function(k){this.window_height=b.height()};j.drag_handler=function(m){var l=m.target.nodeName;if(m.which!==1&&!e){return}if(l==="INPUT"||l==="TEXTAREA"||l==="SELECT"){return}var k=this;var n=true;this.$player=f(m.currentTarget);this.el_init_pos=this.get_actual_pos(this.$player);this.mouse_init_pos=this.get_mouse_pos(m);this.offsetY=this.mouse_init_pos.top-this.el_init_pos.top;this.$body.on(g.move,function(r){var o=k.get_mouse_pos(r);var q=Math.abs(o.left-k.mouse_init_pos.left);var p=Math.abs(o.top-k.mouse_init_pos.top);if(!(q>k.options.distance||p>k.options.distance)){return false}if(n){n=false;k.on_dragstart.call(k,r);return false}if(k.is_dragging==true){k.on_dragmove.call(k,r)}return false})};j.on_dragstart=function(k){k.preventDefault();this.drag_start=true;this.is_dragging=true;var l=this.$container.offset();this.baseX=Math.round(l.left);this.baseY=Math.round(l.top);this.doc_height=f(i).height();if(this.options.helper==="clone"){this.$helper=this.$player.clone().appendTo(this.$container).addClass("helper");this.helper=true}else{this.helper=false}this.scrollOffset=0;this.el_init_offset=this.$player.offset();this.player_width=this.$player.width();this.player_height=this.$player.height();this.player_max_left=(this.$container.width()-this.player_width+this.options.offset_left);if(this.options.start){this.options.start.call(this.$player,k,{helper:this.helper?this.$helper:this.$player})}return false};j.on_dragmove=function(l){var m=this.get_offset(l);this.options.autoscroll&&this.manage_scroll(m);(this.helper?this.$helper:this.$player).css({position:"absolute",left:m.left,top:m.top});var k={position:{left:m.left,top:m.top}};if(this.options.drag){this.options.drag.call(this.$player,l,k)}return false};j.on_dragstop=function(l){var m=this.get_offset(l);this.drag_start=false;var k={position:{left:m.left,top:m.top}};if(this.options.stop){this.options.stop.call(this.$player,l,k)}if(this.helper){this.$helper.remove()}return false};j.enable=function(){this.$container.on(g.start,this.options.items,f.proxy(this.drag_handler,this));this.$body.on(g.end,f.proxy(function(k){this.is_dragging=false;this.$body.off(g.move);if(this.drag_start){this.on_dragstop(k)}},this))};j.disable=function(){this.$container.off(g.start);this.$body.off(g.end)};j.destroy=function(){this.disable();f.removeData(this.$container,"draggable")};f.fn.draggable=function(k){return this.each(function(){if(!f.data(this,"draggable")){f.data(this,"draggable",new a(this,k))}})}}(jQuery,window,document));(function(e,d,a,g){var f={widget_selector:"> li",widget_margins:[10,10],widget_base_dimensions:[400,225],extra_rows:0,extra_cols:0,min_cols:1,min_rows:15,autogenerate_stylesheet:true,avoid_overlapped_widgets:true,serialize_params:function(i,h){return{col:h.col,row:h.row}},collision:{},draggable:{distance:4}};function c(i,h){this.options=e.extend(true,f,h);this.$el=e(i);this.$wrapper=this.$el.parent();this.$widgets=e(this.options.widget_selector,this.$el).addClass("gs_w");this.widgets=[];this.$changed=e([]);this.wrapper_width=this.$wrapper.width();this.min_widget_width=(this.options.widget_margins[0]*2)+this.options.widget_base_dimensions[0];this.min_widget_height=(this.options.widget_margins[1]*2)+this.options.widget_base_dimensions[1];this.init()}c.generated_stylesheets=[];var b=c.prototype;b.init=function(){this.generate_grid_and_stylesheet();this.get_widgets_from_DOM();this.set_dom_grid_height();this.$wrapper.addClass("ready");this.draggable();e(d).bind("resize",throttle(e.proxy(this.recalculate_faux_grid,this),200))};b.disable=function(){this.$wrapper.find(".player-revert").removeClass("player-revert");this.drag_api.disable();return this};b.enable=function(){this.drag_api.enable();return this};b.add_widget=function(j,i,h){var l=this.next_position(i,h);var k=e(j).attr({"data-col":l.col,"data-row":l.row,"data-sizex":l.size_x,"data-sizey":l.size_y}).addClass("gs_w").appendTo(this.$el).hide();this.$widgets=this.$widgets.add(k);this.register_widget(k);this.set_dom_grid_height();return k.fadeIn()};b.next_position=function(p,o){p||(p=1);o||(o=1);var n=this.gridmap;var m=n.length;var l=[];for(var k=1;k<m;k++){var i=n[k].length;for(var h=1;h<=i;h++){var j=this.can_move_to({size_x:p,size_y:o},k,h);if(j){l.push({col:k,row:h,size_y:o,size_x:p})}}}if(l.length){return this.sort_by_row_and_col_asc(l)[0]}return false};b.remove_widget=function(k,l){var j=k instanceof jQuery?k:e(k);var i=j.coords().grid;this.cells_occupied_by_placeholder={};this.$widgets=this.$widgets.not(j);var h=this.widgets_below(j);this.remove_from_gridmap(i);j.fadeOut(e.proxy(function(){j.remove();h.each(e.proxy(function(m,n){this.move_widget_up(e(n),i.size_y)},this));this.set_dom_grid_height();if(l){l.call(this,k)}},this))};b.serialize=function(i){i||(i=this.$widgets);var h=[];i.each(e.proxy(function(j,k){h.push(this.options.serialize_params(e(k),e(k).coords().grid))},this));return h};b.serialize_changed=function(){return this.serialize(this.$changed)};b.register_widget=function(i){var h={col:parseInt(i.attr("data-col"),10),row:parseInt(i.attr("data-row"),10),size_x:parseInt(i.attr("data-sizex"),10),size_y:parseInt(i.attr("data-sizey"),10),el:i};if(this.options.avoid_overlapped_widgets&&!this.can_move_to({size_x:h.size_x,size_y:h.size_y},h.col,h.row)){h=this.next_position(h.size_x,h.size_y);h.el=i;i.attr({"data-col":h.col,"data-row":h.row,"data-sizex":h.size_x,"data-sizey":h.size_y})}i.data("coords",i.coords());i.data("coords").grid=h;this.add_to_gridmap(h,i);this.widgets.push(i);return this};b.update_widget_position=function(h,i){this.for_each_cell_occupied(h,function(j,k){if(!this.gridmap[j]){return this}this.gridmap[j][k]=i});return this};b.remove_from_gridmap=function(h){return this.update_widget_position(h,false)};b.add_to_gridmap=function(i,j){this.update_widget_position(i,j||i.el);if(i.el){var h=this.widgets_below(i.el);h.each(e.proxy(function(k,l){this.move_widget_up(e(l))},this))}};b.draggable=function(){var h=this;var i=e.extend(true,{},this.options.draggable,{offset_left:this.options.widget_margins[0],items:".gs_w",start:function(j,k){h.$widgets.filter(".player-revert").removeClass("player-revert");h.$player=e(this);h.$helper=h.options.draggable.helper==="clone"?e(k.helper):h.$player;h.helper=!h.$helper.is(h.$player);h.on_start_drag.call(h,j,k);h.$el.trigger("gridster:dragstart")},stop:function(j,k){h.on_stop_drag.call(h,j,k);h.$el.trigger("gridster:dragstop")},drag:throttle(function(j,k){h.on_drag.call(h,j,k);h.$el.trigger("gridster:drag")},60)});this.drag_api=this.$el.draggable(i).data("draggable");return this};b.on_start_drag=function(i,k){this.$helper.add(this.$player).add(this.$wrapper).addClass("dragging");this.$player.addClass("player");this.player_grid_data=this.$player.coords().grid;this.placeholder_grid_data=e.extend({},this.player_grid_data);this.$el.css("height",this.$el.height()+(this.player_grid_data.size_y*this.min_widget_height));var h=this.faux_grid;var j=this.$player.data("coords").coords;this.cells_occupied_by_player=this.get_cells_occupied(this.player_grid_data);this.cells_occupied_by_placeholder=this.get_cells_occupied(this.placeholder_grid_data);this.last_cols=[];this.last_rows=[];this.collision_api=this.$helper.collision(h,this.options.collision);this.$preview_holder=e("<li />",{"class":"preview-holder","data-row":this.$player.attr("data-row"),"data-col":this.$player.attr("data-col"),css:{width:j.width,height:j.height}}).appendTo(this.$el);if(this.options.draggable.start){this.options.draggable.start.call(this,i,k)}};b.on_drag=function(i,j){var h={left:j.position.left+this.baseX,top:j.position.top+this.baseY};this.colliders_data=this.collision_api.get_closest_colliders(h);this.on_overlapped_column_change(this.on_start_overlapping_column,this.on_stop_overlapping_column);this.on_overlapped_row_change(this.on_start_overlapping_row,this.on_stop_overlapping_row);if(this.helper&&this.$player){this.$player.css({left:j.position.left,top:j.position.top})}if(this.options.draggable.drag){this.options.draggable.drag.call(this,i,j)}};b.on_stop_drag=function(h,i){this.$helper.add(this.$player).add(this.$wrapper).removeClass("dragging");i.position.left=i.position.left+this.baseX;i.position.top=i.position.top+this.baseY;this.colliders_data=this.collision_api.get_closest_colliders(i.position);this.on_overlapped_column_change(this.on_start_overlapping_column,this.on_stop_overlapping_column);this.on_overlapped_row_change(this.on_start_overlapping_row,this.on_stop_overlapping_row);this.$player.addClass("player-revert").removeClass("player").attr({"data-col":this.placeholder_grid_data.col,"data-row":this.placeholder_grid_data.row}).css({left:"",top:""});this.$changed=this.$changed.add(this.$player);this.cells_occupied_by_player=this.get_cells_occupied(this.placeholder_grid_data);this.set_cells_player_occupies(this.placeholder_grid_data.col,this.placeholder_grid_data.row);this.$player.coords().grid.row=this.placeholder_grid_data.row;this.$player.coords().grid.col=this.placeholder_grid_data.col;this.$player=null;this.$preview_holder.remove();this.set_dom_grid_height();if(this.options.draggable.stop){this.options.draggable.stop.call(this,h,i)}};b.on_overlapped_column_change=function(j,n){if(!this.colliders_data.length){return}var l=this.get_targeted_columns(this.colliders_data[0].el.data.col);var m=this.last_cols.length;var k=l.length;var h;for(h=0;h<k;h++){if(e.inArray(l[h],this.last_cols)===-1){(j||e.noop).call(this,l[h])}}for(h=0;h<m;h++){if(e.inArray(this.last_cols[h],l)===-1){(n||e.noop).call(this,this.last_cols[h])}}this.last_cols=l;return this};b.on_overlapped_row_change=function(j,k){if(!this.colliders_data.length){return}var m=this.get_targeted_rows(this.colliders_data[0].el.data.row);var n=this.last_rows.length;var l=m.length;var h;for(h=0;h<l;h++){if(e.inArray(m[h],this.last_rows)===-1){(j||e.noop).call(this,m[h])}}for(h=0;h<n;h++){if(e.inArray(this.last_rows[h],m)===-1){(k||e.noop).call(this,this.last_rows[h])}}this.last_rows=m};b.set_player=function(j,p){this.empty_cells_player_occupies();var o=this;var n=o.colliders_data[0].el.data;var l=n.col;var h=p||n.row;this.player_grid_data={col:l,row:h,size_y:this.player_grid_data.size_y,size_x:this.player_grid_data.size_x};this.cells_occupied_by_player=this.get_cells_occupied(this.player_grid_data);var m=this.get_widgets_overlapped(this.player_grid_data);var k=this.widgets_constraints(m);this.manage_movements(k.can_go_up,l,h);this.manage_movements(k.can_not_go_up,l,h);if(!m.length){var i=this.can_go_player_up(this.player_grid_data);if(i!==false){h=i}this.set_placeholder(l,h)}return{col:l,row:h}};b.widgets_constraints=function(i){var h=e([]);var k;var j=[];var l=[];i.each(e.proxy(function(o,m){var p=e(m);var n=p.coords().grid;if(this.can_go_widget_up(n)){h=h.add(p);j.push(n)}else{l.push(n)}},this));k=i.not(h);return{can_go_up:this.sort_by_row_asc(j),can_not_go_up:this.sort_by_row_desc(l)}};b.sort_by_row_asc=function(h){h=h.sort(function(j,i){if(j.row>i.row){return 1}return -1});return h};b.sort_by_row_and_col_asc=function(h){h=h.sort(function(j,i){if(j.row>i.row||j.row==i.row&&j.col>i.col){return 1}return -1});return h};b.sort_by_col_asc=function(h){h=h.sort(function(j,i){if(j.col>i.col){return 1}return -1});return h};b.sort_by_row_desc=function(h){h=h.sort(function(j,i){if(j.row+j.size_y<i.row+i.size_y){return 1}return -1});return h};b.manage_movements=function(i,j,h){e.each(i,e.proxy(function(n,l){var m=l;var p=m.el;var k=this.can_go_widget_up(m);if(k){this.move_widget_to(p,k);this.set_placeholder(j,k+m.size_y)}else{var o=this.can_go_player_up(this.player_grid_data);if(!o){var q=(h+this.player_grid_data.size_y)-m.row;this.move_widget_down(p,q);this.set_placeholder(j,h)}}},this));return this};b.is_player=function(i,j){if(j&&!this.gridmap[i]){return false}var h=j?this.gridmap[i][j]:i;return h&&(h.is(this.$player)||h.is(this.$helper))};b.is_player_in=function(h,i){var j=this.cells_occupied_by_player;return e.inArray(h,j.cols)>=0&&e.inArray(i,j.rows)>=0};b.is_placeholder_in=function(h,i){var j=this.cells_occupied_by_placeholder||{};return this.is_placeholder_in_col(h)&&e.inArray(i,j.rows)>=0};b.is_placeholder_in_col=function(h){var i=this.cells_occupied_by_placeholder||[];return e.inArray(h,i.cols)>=0};b.is_empty=function(h,i){if(typeof this.gridmap[h]!=="undefined"&&typeof this.gridmap[h][i]!=="undefined"&&this.gridmap[h][i]===false){return true}return false};b.is_occupied=function(h,i){if(!this.gridmap[h]){return false}if(this.gridmap[h][i]){return true}return false};b.is_widget=function(i,j){var h=this.gridmap[i];if(!h){return false}h=h[j];if(h){return h}return false};b.is_widget_under_player=function(h,i){if(this.is_widget(h,i)){return this.is_player_in(h,i)}return false};b.get_widgets_under_player=function(){var i=this.cells_occupied_by_player;var h=e([]);e.each(i.cols,e.proxy(function(k,j){e.each(i.rows,e.proxy(function(l,m){if(this.is_widget(j,m)){h=h.add(this.gridmap[j][m])}},this))},this));return h};b.set_placeholder=function(k,m){var j=e.extend({},this.placeholder_grid_data);var h=this.widgets_below({col:j.col,row:j.row,size_y:j.size_y,size_x:j.size_x});var i=(k+j.size_x-1);if(i>this.cols){k=k-(i-k)}var n=this.placeholder_grid_data.row<m;var l=this.placeholder_grid_data.col!==k;this.placeholder_grid_data.col=k;this.placeholder_grid_data.row=m;this.cells_occupied_by_placeholder=this.get_cells_occupied(this.placeholder_grid_data);this.$preview_holder.attr({"data-row":m,"data-col":k});if(n||l){h.each(e.proxy(function(o,p){this.move_widget_up(e(p),this.placeholder_grid_data.col-k+j.size_y)},this))}};b.can_go_player_up=function(m){var j=m.row+m.size_y-1;var h=true;var i=[];var l=10000;var k=this.get_widgets_under_player();this.for_each_column_occupied(m,function(p){var n=this.gridmap[p];var o=j+1;i[p]=[];while(--o>0){if(this.is_empty(p,o)||this.is_player(p,o)||this.is_widget(p,o)&&n[o].is(k)){i[p].push(o);l=o<l?o:l}else{break}}if(i[p].length===0){h=false;return true}i[p].sort()});if(!h){return false}return this.get_valid_rows(m,i,l)};b.can_go_widget_up=function(l){var j=l.row+l.size_y-1;var h=true;var i=[];var k=10000;this.for_each_column_occupied(l,function(o){var m=this.gridmap[o];i[o]=[];var n=j+1;while(--n>0){if(this.is_occupied(o,n)&&!this.is_player(o,n)){break}if(!this.is_player(o,n)&&!this.is_placeholder_in(o,n)){i[o].push(n)}if(n<k){k=n}}if(i[o].length===0){h=false;return true}i[o].sort()});if(!h){return false}return this.get_valid_rows(l,i,k)};b.get_valid_rows=function(i,q,p){var n=i.row;var l=i.row+i.size_y-1;var o=i.size_y;var h=p-1;var j=[];while(++h<=l){var m=true;e.each(q,function(r,s){if(s&&e.inArray(h,s)===-1){m=false}});if(m===true){j.push(h);if(j.length===o){break}}}var k=false;if(o===1){if(j[0]!==n){k=j[0]||false}}else{if(j[0]!==n){k=this.get_consecutive_numbers_index(j,o)}}return k};b.get_consecutive_numbers_index=function(k,l){var j=k.length;var h=[];var o=true;var n=-1;for(var m=0;m<j;m++){if(o||k[m]===n+1){h.push(m);if(h.length===l){break}o=false}else{h=[];o=true}n=k[m]}return h.length>=l?k[h[0]]:false};b.get_widgets_overlapped=function(){var k;var h=e([]);var j=[];var i=this.cells_occupied_by_player.rows.slice(0);i.reverse();e.each(this.cells_occupied_by_player.cols,e.proxy(function(m,l){e.each(i,e.proxy(function(n,p){if(!this.gridmap[l]){return true}var o=this.gridmap[l][p];if(this.is_occupied(l,p)&&!this.is_player(o)&&e.inArray(o,j)===-1){h=h.add(o);j.push(o)}},this))},this));return h};b.on_start_overlapping_column=function(h){this.set_player(h,false)};b.on_start_overlapping_row=function(h){this.set_player(false,h)};b.on_stop_overlapping_column=function(i){this.set_player();var h=this;this.for_each_widget_below(i,this.cells_occupied_by_player.rows[0],function(k,j){h.move_widget_up(this,h.player_grid_data.size_y)})};b.on_stop_overlapping_row=function(k){this.set_player();var i=this;var j=this.cells_occupied_by_player.cols;for(var l=0,h=j.length;l<h;l++){this.for_each_widget_below(j[l],k,function(n,m){i.move_widget_up(this,i.player_grid_data.size_y)})}};b.move_widget_to=function(n,m){var h=this;var j=n.coords().grid;var l=m-j.row;var k=this.widgets_below(n);var i=this.can_move_to(j,j.col,m,n);if(i===false){return false}this.remove_from_gridmap(j);j.row=m;this.add_to_gridmap(j);n.attr("data-row",m);this.$changed=this.$changed.add(n);k.each(function(p,r){var q=e(r);var o=q.coords().grid;var s=h.can_go_widget_up(o);if(s&&s!==o.row){h.move_widget_to(q,s)}});return this};b.move_widget_up=function(m,l){var k=m.coords().grid;var h=k.row;var i=[];var j=true;l||(l=1);if(!this.can_go_up(m)){return false}this.for_each_column_occupied(k,function(n){if(e.inArray(m,i)===-1){var o=m.coords().grid;var p=h-l;p=this.can_go_up_to_row(o,n,p);if(!p){return true}var q=this.widgets_below(m);this.remove_from_gridmap(o);o.row=p;this.add_to_gridmap(o);m.attr("data-row",o.row);this.$changed=this.$changed.add(m);i.push(m);q.each(e.proxy(function(r,s){this.move_widget_up(e(s),l)},this))}})};b.move_widget_down=function(h,l){var i=h.coords().grid;var o=i.row;var n=[];var m=l;if(!h){return false}if(e.inArray(h,n)===-1){var k=h.coords().grid;var p=o+l;var j=this.widgets_below(h);this.remove_from_gridmap(k);j.each(e.proxy(function(r,u){var t=e(u);var s=t.coords().grid;var q=this.displacement_diff(s,k,m);if(q>0){this.move_widget_down(t,q)}},this));k.row=p;this.update_widget_position(k,h);h.attr("data-row",k.row);this.$changed=this.$changed.add(h);n.push(h)}};b.can_go_up_to_row=function(j,i,q){var o=this.gridmap;var s=true;var l=[];var n=j.row;var h;this.for_each_column_occupied(j,function(t){var r=o[t];l[t]=[];h=n;while(h--){if(this.is_empty(t,h)&&!this.is_placeholder_in(t,h)){l[t].push(h)}else{break}}if(!l[t].length){s=false;return true}});if(!s){return false}h=q;for(h=1;h<n;h++){var m=true;for(var p=0,k=l.length;p<k;p++){if(l[p]&&e.inArray(h,l[p])===-1){m=false}}if(m===true){s=h;break}}return s};b.displacement_diff=function(k,l,n){var i=k.row;var m=[];var j=l.row+l.size_y;this.for_each_column_occupied(k,function(o){var q=0;for(var p=j;p<i;p++){if(this.is_empty(o,p)){q=q+1}}m.push(q)});var h=Math.max.apply(Math,m);n=(n-h);return n>0?n:0};b.widgets_below=function(j){var l=e.isPlainObject(j)?j:j.coords().grid;var i=this;var m=this.gridmap;var k=l.row+l.size_y-1;var h=e([]);this.for_each_column_occupied(l,function(n){i.for_each_widget_below(n,k,function(p,o){if(!i.is_player(this)&&e.inArray(this,h)===-1){h=h.add(this);return true}})});return this.sort_by_row_asc(h)};b.set_cells_player_occupies=function(h,i){this.remove_from_gridmap(this.placeholder_grid_data);this.placeholder_grid_data.col=h;this.placeholder_grid_data.row=i;this.add_to_gridmap(this.placeholder_grid_data,this.$player);return this};b.empty_cells_player_occupies=function(){this.remove_from_gridmap(this.placeholder_grid_data);return this};b.can_go_up=function(j){var m=j.coords().grid;var k=m.row;var l=k-1;var n=this.gridmap;var i=[];var h=true;if(k===1){return false}this.for_each_column_occupied(m,function(o){var p=this.is_widget(o,l);if(this.is_occupied(o,l)||this.is_player(o,l)||this.is_placeholder_in(o,l)){h=false;return true}});return h};b.can_move_to=function(l,j,o){var n=this.gridmap;var m=l.el;var k={size_y:l.size_y,size_x:l.size_x,col:j,row:o};var h=true;var i=j+l.size_x-1;if(i>this.cols){return false}this.for_each_cell_occupied(k,function(r,q){var p=this.is_widget(r,q);if(p&&(!l.el||p.is(m))){h=false}});return h};b.get_targeted_columns=function(k){var h=(k||this.player_grid_data.col)+(this.player_grid_data.size_x-1);var j=[];for(var i=k;i<=h;i++){j.push(i)}return j};b.get_targeted_rows=function(i){var h=(i||this.player_grid_data.row)+(this.player_grid_data.size_y-1);var j=[];for(var k=i;k<=h;k++){j.push(k)}return j};b.get_cells_occupied=function(l){var j={cols:[],rows:[]};var k;if(arguments[1] instanceof jQuery){l=arguments[1].coords().grid}for(k=0;k<l.size_x;k++){var h=l.col+k;j.cols.push(h)}for(k=0;k<l.size_y;k++){var m=l.row+k;j.rows.push(m)}return j};b.for_each_cell_occupied=function(h,i){this.for_each_column_occupied(h,function(j){this.for_each_row_occupied(h,function(k){i.call(this,j,k)})});return this};b.for_each_column_occupied=function(k,l){for(var j=0;j<k.size_x;j++){var h=k.col+j;l.call(this,h,k)}};b.for_each_row_occupied=function(j,l){for(var h=0;h<j.size_y;h++){var k=j.row+h;l.call(this,k,j)}};b._traversing_widgets=function(n,q,j,t,r){var s=this.gridmap;if(!s[j]){return}var m,p;var l=n+"/"+q;if(arguments[2] instanceof jQuery){var h=arguments[2].coords().grid;j=h.col;t=h.row;r=arguments[3]}var i=[];var o=t;var k={"for_each/above":function(){while(o--){if(o>0&&this.is_widget(j,o)&&e.inArray(s[j][o],i)===-1){m=r.call(s[j][o],j,o);i.push(s[j][o]);if(m){break}}}},"for_each/below":function(){for(o=t+1,p=s[j].length;o<p;o++){if(this.is_widget(j,o)&&e.inArray(s[j][o],i)===-1){m=r.call(s[j][o],j,o);i.push(s[j][o]);if(m){break}}}}};if(k[l]){k[l].call(this)}};b.for_each_widget_above=function(h,i,j){this._traversing_widgets("for_each","above",h,i,j);return this};b.for_each_widget_below=function(h,i,j){this._traversing_widgets("for_each","below",h,i,j);return this};b.get_highest_occupied_cell=function(){var i;var k=this.gridmap;var j=[];var m=[];for(var l=k.length-1;l>=1;l--){for(i=k[l].length-1;i>=1;i--){if(this.is_widget(l,i)){j.push(i);m[i]=l;break}}}var h=Math.max.apply(Math,j);this.highest_occupied_cell={col:m[h],row:h};return this.highest_occupied_cell};b.get_widgets_from=function(i,k){var j=this.gridmap;var h=e();if(i){h=h.add(this.$widgets.filter(function(){var l=e(this).attr("data-col");return(l===i||l>i)}))}if(k){h=h.add(this.$widgets.filter(function(){var l=e(this).attr("data-row");return(l===k||l>k)}))}return h};b.set_dom_grid_height=function(){var h=this.get_highest_occupied_cell().row;this.$el.css("height",h*this.min_widget_height);return this};b.generate_stylesheet=function(h){var r="";var k=10;var m=6;var o=6;var l;var q;h||(h={});h.cols||(h.cols=this.cols);h.rows||(h.rows=this.rows);h.namespace||(h.namespace="");h.widget_base_dimensions||(h.widget_base_dimensions=this.options.widget_base_dimensions);h.widget_margins||(h.widget_margins=this.options.widget_margins);h.min_widget_width=(h.widget_margins[0]*2)+h.widget_base_dimensions[0];h.min_widget_height=(h.widget_margins[1]*2)+h.widget_base_dimensions[1];var j=e.param(h);if(e.inArray(j,c.generated_stylesheets)>=0){return false}c.generated_stylesheets.push(j);for(l=h.cols+k;l>=0;l--){r+=(h.namespace+' [data-col="'+(l+1)+'"] { left:'+((l*h.widget_base_dimensions[0])+(l*h.widget_margins[0])+((l+1)*h.widget_margins[0]))+"px;} ")}for(l=h.rows+k;l>=0;l--){r+=(h.namespace+' [data-row="'+(l+1)+'"] { top:'+((l*h.widget_base_dimensions[1])+(l*h.widget_margins[1])+((l+1)*h.widget_margins[1]))+"px;} ")}for(var n=1;n<m;n++){r+=(h.namespace+' [data-sizey="'+n+'"] { height:'+(n*h.widget_base_dimensions[1]+(n-1)*(h.widget_margins[1]*2))+"px;}")}for(var p=1;p<o;p++){r+=(h.namespace+' [data-sizex="'+p+'"] { width:'+(p*h.widget_base_dimensions[0]+(p-1)*(h.widget_margins[0]*2))+"px;}")}return this.add_style_tag(r)};b.add_style_tag=function(i){var j=a;var h=j.createElement("style");j.getElementsByTagName("head")[0].appendChild(h);h.setAttribute("type","text/css");if(h.styleSheet){h.styleSheet.cssText=i}else{h.appendChild(a.createTextNode(i))}return this};b.generate_faux_grid=function(j,k){this.faux_grid=[];this.gridmap=[];var h;var l;for(h=k;h>0;h--){this.gridmap[h]=[];for(l=j;l>0;l--){var i=e({left:this.baseX+((h-1)*this.min_widget_width),top:this.baseY+(l-1)*this.min_widget_height,width:this.min_widget_width,height:this.min_widget_height,col:h,row:l,original_col:h,original_row:l}).coords();this.gridmap[h][l]=false;this.faux_grid.push(i)}}return this};b.recalculate_faux_grid=function(){var h=this.$wrapper.width();this.baseX=(e(d).width()-h)/2;this.baseY=this.$wrapper.offset().top;e.each(this.faux_grid,e.proxy(function(j,k){this.faux_grid[j]=k.update({left:this.baseX+(k.data.col-1)*this.min_widget_width,top:this.baseY+(k.data.row-1)*this.min_widget_height})},this));return this};b.get_widgets_from_DOM=function(){this.$widgets.each(e.proxy(function(h,j){this.register_widget(e(j))},this));return this};b.generate_grid_and_stylesheet=function(){var n=this.$wrapper.width();var i=this.$wrapper.height();var m=Math.floor(n/this.min_widget_width)+this.options.extra_cols;var l=Math.floor(i/this.min_widget_height)+this.options.extra_rows;var k=this.$widgets.map(function(){return e(this).attr("data-col")});k=Array.prototype.slice.call(k,0);k.length||(k=[0]);var j=this.$widgets.map(function(){return e(this).attr("data-row")});j=Array.prototype.slice.call(j,0);j.length||(j=[0]);var h=Math.max.apply(Math,k);var o=Math.max.apply(Math,j);this.cols=Math.max(h,m,this.options.min_cols);this.rows=Math.max(o,l,this.options.min_rows);this.baseX=(e(d).width()-n)/2;this.baseY=this.$wrapper.offset().top;if(this.options.autogenerate_stylesheet){this.generate_stylesheet()}return this.generate_faux_grid(this.rows,this.cols)};e.fn.gridster=function(h){return this.each(function(){if(!e(this).data("gridster")){e(this).data("gridster",new c(this,h))}})}}(jQuery,window,document));