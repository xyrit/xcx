var my_url = 'http://www.yj251.com/index.php';
var pic_url = 'http://www.yj251.com';
var div_1  =  document.createElement("div");
div_1.className = 'mui-hidden';
div_1.id = 'CenterLoading';
div_1.innerHTML ='<div id="LoadingIn"><div></div><div></div><div></div></div>';
document.body.appendChild(div_1);

function get_param(name) {
var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"); 
var r = window.location.search.substr(1).match(reg); 
if (r != null) return unescape(r[2]); return null; 
} 

(function($, window, document, name, undefined)	{
	var CLASS_CONTROL_ITEM = 'mui-control-item';
	var CLASS_SEGMENTED_CONTROL = 'mui-segmented-control';
	var CLASS_SEGMENTED_CONTROL_VERTICAL = 'mui-segmented-control-vertical';
	var CLASS_CONTROL_CONTENT = 'mui-control-content';
	var CLASS_TAB_BAR = 'mui-bar-tab';
	var CLASS_TAB_ITEM = 'mui-tab-item';
	var CLASS_SLIDER_ITEM = 'mui-slider-item';
	var handle = function(event, target) {
			if (target.classList && (target.classList.contains(CLASS_CONTROL_ITEM) || target.classList.contains(CLASS_TAB_ITEM))) {
				if(target.parentNode && target.parentNode.classList && target.parentNode.classList.contains(CLASS_SEGMENTED_CONTROL_VERTICAL)) {
					//vertical 如果preventDefault会导致无法滚动
				} else {
					//event.preventDefault(); //防止连接打开				
				}
				
				//	if(target.hash) {
				return target;
				//	}
			}
			return false;
	};
	$.registerTarget({
		name: name,
		index: 80,
		handle: handle,
		target: false
	});
	window.addEventListener('tap', function(e){
		
		var targetTab = $.targets.tab;
		
		if (!targetTab){
			return;
		}
		var activeTab;
		var activeBodies;
		var targetBody;
		var className = 'mui-active';
		var classSelector = '.' + className;
		var segmentedControl = targetTab.parentNode;
		
		for (; segmentedControl && segmentedControl !== document; segmentedControl = segmentedControl.parentNode) {
			
			if (segmentedControl.classList.contains(CLASS_SEGMENTED_CONTROL)) {
				activeTab = segmentedControl.querySelector(classSelector + '.' + CLASS_CONTROL_ITEM);
				break;
			} else if (segmentedControl.classList.contains(CLASS_TAB_BAR)) {
				
				activeTab = segmentedControl.querySelector(classSelector + '.' + CLASS_TAB_ITEM);
			}
		}
		if(targetTab.getAttribute('href')=='member.html'&&!localStorage.getItem('token')){
				return;
		}
	
		if (activeTab) {			
			activeTab.classList.remove(className);
		}
		
		var isLastActive = targetTab === activeTab;
	
		if (targetTab) {
			targetTab.classList.add(className);
		}
	
		if (!targetTab.hash) {
			return;
		}
		targetBody = document.getElementById(targetTab.hash.replace('#', ''));
	
		if (!targetBody) {
			return;
		}
		if (!targetBody.classList.contains(CLASS_CONTROL_CONTENT)) { //tab bar popover
			targetTab.classList[isLastActive ? 'remove' : 'add'](className);
			return;
		}
		if (isLastActive) { //same
			return;
		}
		var parentNode = targetBody.parentNode;
		activeBodies = parentNode.querySelectorAll('.' + CLASS_CONTROL_CONTENT + classSelector);
		for (var i = 0; i < activeBodies.length; i++) {
			var activeBody = activeBodies[i];
			activeBody.parentNode === parentNode && activeBody.classList.remove(className);
		}

		targetBody.classList.add(className);
		var contents = [];
		var _contents = parentNode.querySelectorAll('.' + CLASS_CONTROL_CONTENT);
		for (var i = 0; i < _contents.length; i++) { //查找直属子节点
			_contents[i].parentNode === parentNode && (contents.push(_contents[i]));
		}
		$.trigger(targetBody, $.eventName('shown', name), {
			tabNumber: Array.prototype.indexOf.call(contents, targetBody)
		});
		e.detail && e.detail.gesture.preventDefault(); //fixed hashchange
	});

})(mui, window, document, 'tab');

Array.prototype.forEach.call(document.getElementsByTagName('a'),function(el){
				el.addEventListener('tap',function(){
								id = this.dataset.id;
								if(!id){return false;};
								window.parent.location.href=id;
			})
})

function quit(){
//						var first=0;
//						mui.back = function() {
//								
//								if(first==0){
//									var time = new Date().getTime();
//									first = 1;
//									mui.toast('再按一次退出应用');
//									setTimeout(function(){
//										first = 0;
//									},2000);
//								}else{
//									plus.runtime.quit();
//								}
//								
//						};
mui.back = function(){
	var parentView = plus.webview.getWebviewById(plus.runtime.appid);
		parentView.evalJS('mui.back();');
	}
}

yjshop = function(id){
	return document.getElementById(id);
}
var log = function(data){
		console.log(JSON.stringify(data));
}

var login = function(back){
			back?back:'member.html';
			plus.webview.create('login.html','login.html',{},{'back':back});
			plus.webview.show('login.html','slide-in-right');
}

function loading(){
		yjshop('CenterLoading').className = 'shade';
}

function loading_hide(){
		yjshop('CenterLoading').className = 'mui-hidden';
}
function good_content(id){
		window.parent.location.href="goods_content.html?goods_id="+id;
		//plus.webview.show('goods_content.html','slide-in-right');
}