!function(a){function b(a){return 10>a&&(a="0"+a),a}function c(a){return a>11?"pm":"am"}function d(a){var b="th";switch(parseInt(a)){case 1:case 21:case 31:b="st";break;case 2:case 22:b="nd";break;case 3:case 23:b="rd"}return b}function e(a){for(var b=new Date(a,0,1);1!=b.getDay();)b.setDate(b.getDate()+1);return b.valueOf()}function f(a){return 0==a?a=24:a>12&&(a-=12),a}var g=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"],h=["January","February","March","April","May","June","July","August","September","October","November","December"];a.PHPDate=function(a,i){var j="",k="";a=a.replace(/r/g,"D, j M Y H;i:s O");for(var l=0;l<a.length;l++){switch(j=a.charAt(l)){case"a":j=c(i.getHours());break;case"c":j=i.getFullYear()+"-"+b(i.getMonth())+"-"+b(i.getDate())+"T"+b(i.getHours())+":"+b(i.getMinutes())+":"+b(i.getSeconds());var m=i.toString().split(" ")[5];j+=m.indexOf("-")>-1?m.substr(m.indexOf("-")):m.indexOf("+")>-1?m.substr(m.indexOf("+")):"+0000";break;case"d":j=b(i.getDate());break;case"g":j=f(i.getHours());break;case"h":j=b(f(i.getHours()));break;case"i":j=b(i.getMinutes());break;case"j":j=i.getDate();break;case"l":j=g[i.getDay()];break;case"m":j=b(i.getMonth()+1);break;case"n":j=i.getMonth()+1;break;case"o":j=new Date(e(i.getFullYear()))>i?i.getFullYear()-1:i.getFullYear();break;case"s":j=b(i.getSeconds());break;case"t":var n=new Date(i.valueOf());n.setMonth(n.getMonth()+1),n.setDate(0),j=n.getDate();break;case"u":j=i.getMilliseconds();break;case"w":j=i.getDay();break;case"y":j=i.getFullYear().toString().substr(2,2);break;case"z":var o=new Date(i.getFullYear(),0,1,0,0,0,0),p=new Date(i.getFullYear(),i.getMonth(),i.getDate(),0,0,0,0);j=Math.round((p.valueOf()-o.valueOf())/1e3/60/60/24);break;case"A":j=c(i.getHours()).toUpperCase();break;case"B":j=Math.floor((60*i.getHours()*60*1e3+60*i.getMinutes()*1e3+1e3*i.getSeconds()+i.getMilliseconds())/86400);break;case"D":j=g[i.getDay()].substr(0,3);break;case"F":j=h[i.getMonth()];break;case"G":j=i.getHours();break;case"H":j=b(i.getHours());break;case"I":var q=new Date(i.getFullYear(),0,1),r=new Date(i.getFullYear(),i.getMonth(),i.getDate()),s=(r.valueOf()-q.valueOf())/1e3/60/60/24;j=s==Math.round(s)?0:1;break;case"L":j=29==new Date(i.getFullYear(),2,0).getDate()?1:0;break;case"M":j=h[i.getMonth()].substr(0,3);break;case"N":j=0==i.getDay()?7:i.getDay();break;case"O":var m=i.toString().split(" ")[5];j=m.indexOf("-")>-1?m.substr(m.indexOf("-")):m.indexOf("+")>-1?m.substr(m.indexOf("+")):"+0000";break;case"P":var m=i.toString().split(" ")[5];if(m.indexOf("-")>-1){var t=m.substr(m.indexOf("-")+1).split("");j="-"+t[0]+t[1]+":"+t[2]+t[3]}else if(m.indexOf("+")>-1){var t=m.substr(m.indexOf("+")+1).split("");j="+"+t[0]+t[1]+":"+t[2]+t[3]}else j="+00:00";break;case"S":j=d(i.getDate());break;case"T":j=i.toString().split(" ")[5],j.indexOf("+")>-1?j=j.substr(0,j.indexOf("+")):j.indexOf("-")>-1&&(j=j.substr(0,j.indexOf("-")));break;case"U":j=Math.floor(i.getTime()/1e3);break;case"W":var q=new Date(e(i.getFullYear())),r=new Date(i.getFullYear(),i.getMonth(),i.getDate());j=Math.ceil(Math.round((r.valueOf()-q.valueOf())/1e3/60/60/24)/7);break;case"Y":j=i.getFullYear();break;case"Z":j=i.getTimezoneOffset()<0?Math.abs(60*i.getTimezoneOffset()):0-60*i.getTimezoneOffset()}k+=j.toString()}return k}}(jQuery);
//# sourceMappingURL=jquery.phpdate.map