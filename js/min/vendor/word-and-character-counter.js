!function(a){a.fn.extend({counter:function(b){var c={type:"char",count:"down",goal:140,text:!0,target:!1,append:!0,translation:"",msg:""},d="",e="",f=!1,b=a.extend({},c,b),g={init:function(c){var e=c.attr("id"),f=e+"_count";g.isLimitless(),d=a("<span id="+f+"/>");var h=a("<div/>").attr("id",e+"_counter").addClass("input-counter").append(d).append(" "+g.setMsg());b.target&&a(b.target).length?b.append?a(b.target).append(h):a(b.target).prepend(h):b.append?h.insertAfter(c):h.insertBefore(c),g.bind(c)},bind:function(a){a.bind("keypress.counter keydown.counter keyup.counter blur.counter focus.counter change.counter paste.counter",g.updateCounter),a.bind("keydown.counter",g.doStopTyping),a.trigger("keydown")},isLimitless:function(){return"sky"===b.goal?(b.count="up",f=!0):void 0},setMsg:function(){if(""!==b.msg)return b.msg;if(b.text===!1)return"";if(f)return""!==b.msg?b.msg:"";switch(this.text=b.translation||"character word left max",this.text=this.text.split(" "),this.chars="s ( )".split(" "),this.msg=null,b.type){case"char":b.count===c.count&&b.text?this.msg=this.text[0]+this.chars[1]+this.chars[0]+this.chars[2]+" "+this.text[2]:"up"===b.count&&b.text&&(this.msg=this.text[0]+this.chars[0]+" "+this.chars[1]+b.goal+" "+this.text[3]+this.chars[2]);break;case"word":b.count===c.count&&b.text?this.msg=this.text[1]+this.chars[1]+this.chars[0]+this.chars[2]+" "+this.text[2]:"up"===b.count&&b.text&&(this.msg=this.text[1]+this.chars[1]+this.chars[0]+this.chars[2]+" "+this.chars[1]+b.goal+" "+this.text[3]+this.chars[2])}return this.msg},getWords:function(b){return""!==b?a.trim(b).replace(/\s+/g," ").split(" ").length:0},updateCounter:function(){var f=a(this);(0>e||e>b.goal)&&g.passedGoal(f);var h=a(d).parent();b.type===c.type?b.count===c.count?(e=b.goal-f.val().length,d.text(0>=e?"0":e)):"up"===b.count&&(e=f.val().length,d.text(e)):"word"===b.type&&(b.count===c.count?(e=g.getWords(f.val()),e<=b.goal?(e=b.goal-e,d.text(e)):d.text("0")):"up"===b.count&&(e=g.getWords(f.val()),d.text(e)));var i=e/b.goal;0==i?(jQuery(h).removeClass("near-limit"),jQuery(h).addClass("at-limit")):.34>=i?(jQuery(h).removeClass("at-limit"),jQuery(h).addClass("near-limit")):(jQuery(h).removeClass("near-limit"),jQuery(h).removeClass("at-limit"))},doStopTyping:function(a){var d=[46,8,9,35,36,37,38,39,40,32];return g.isGoalReached(a)&&a.keyCode!==d[0]&&a.keyCode!==d[1]&&a.keyCode!==d[2]&&a.keyCode!==d[3]&&a.keyCode!==d[4]&&a.keyCode!==d[5]&&a.keyCode!==d[6]&&a.keyCode!==d[7]&&a.keyCode!==d[8]?b.type===c.type?!1:a.keyCode!==d[9]&&a.keyCode!==d[1]&&b.type!=c.type?!0:!1:void 0},isGoalReached:function(a,d){return f?!1:b.count===c.count?(d=0,d>=e?!0:!1):(d=b.goal,e>=d?!0:!1)},wordStrip:function(b,c){var d=c.replace(/\s+/g," ").split(" ").length;return c=a.trim(c),0>=b||b===d?c:(c=a.trim(c).split(" "),c.splice(b,d,""),a.trim(c.join(" ")))},passedGoal:function(a){var c=a.val();"word"===b.type&&a.val(g.wordStrip(b.goal,c)),"char"===b.type&&a.val(c.substring(0,b.goal)),"down"===b.type&&d.val("0"),"up"===b.type&&d.val(b.goal)}};return this.each(function(){g.init(a(this))})}})}(jQuery);
//# sourceMappingURL=word-and-character-counter.map