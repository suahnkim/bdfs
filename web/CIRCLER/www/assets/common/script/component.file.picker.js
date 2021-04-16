'use strict';

function ComponentFilePicker(configs){
	configs = configs || {};
	configs.id = configs.id || ".jca-filepicker";
	configs.selector = configs.selector || ".jca-filepicker-area";
	configs.output = configs.output || ".jca-filepicker-output";
	configs.handle = configs.handle || function(event){
		var self = this;
		//var input_target = configs.inputs[output_idx];
		var input_target = event.target;
		var idx = parseInt(event.target.getAttribute("data-idx"));
		var files = event.target.files;
		var output = configs.outputs[idx];
		output.innerHTML = '';

		if(files.length > 0){
			input_target.setAttribute("data-selected-file-count", files.length);
		}else{
			input_target.removeAttribute("data-selected-file-count");
		}
		var sucess_ele = input_target.parentElement.querySelector(".file-dummy .success");
		sucess_ele.innerHTML = files.length + "개의 파일이 선택되었습니다.";
		for(var i = 0, f; f = files[i]; i++){
			if(f.type.match('image.*')){
				var reader = new FileReader();
				reader.onload = (function(theFile){
					return function(e){
						var image = new Image();
						image.src = e.target.result;
						image.onload = function(){
							var data_selected_image_sizes = input_target.getAttribute("data-selected-image-sizes") || "";
							data_selected_image_sizes = data_selected_image_sizes + "|" + this.width + 'x' + this.height;
							data_selected_image_sizes = data_selected_image_sizes.replace(/^\|/i, "");
							input_target.setAttribute("data-selected-image-sizes", data_selected_image_sizes);
							var iele = document.createElement('li');
							iele.style.position = 'relative';
							iele.style.fontSize = 0;
							iele.className = "image";
							iele.innerHTML = ['<img class="thumb" src="', e.target.result,
											'" title="', escape(theFile.name), '"/>','<span style="position:absolute;bottom:2px;right:7px;font-size:9px;background-color:rgba(255, 255, 255, 0.8);padding:2px;">' + this.width + 'x' + this.height + '</span>'].join('');
							output.insertBefore(iele, null);
						};
					};
				})(f);
				reader.readAsDataURL(f);
			}else{
				var iele = document.createElement('li');
						iele.innerHTML = escape(f.name);
						output.insertBefore(iele, null);
			}
		}
	}

	configs.inputs = [];
	configs.outputs = [];

	this.isNumber = function(s){
		s += '';
		s = s.replace(/^\s*|\s*$/g, '');
		if (s == '' || isNaN(s)) return false;
		return true;
	}

	this.init = function(){
		var self = this;
		self.eles = document.querySelectorAll(configs.id);
		var idx = 0;
		for(var i=0;i<self.eles.length;i++){
			var ele = self.eles[i];
			var file_input = ele.querySelector("input[type='file']");
			configs.outputs.push(ele.querySelector(configs.output));
			file_input.setAttribute("data-idx", idx);
			file_input.addEventListener('change', function(event){
				configs.handle(event);
			}, false);
			idx++;
		}
	}
	this.construct = this.init();
}

window.comfp = new ComponentFilePicker({});