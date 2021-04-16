function commonFormValidation(params) {
	params = params || {};
	params.form = params.form || null;
	params.alert = params.alert || function(message, func){
		alert(message.replace(/<\/?[^>]+>/gi, "").replace(/\\n/gi, "\n"));
		if(typeof func == "function") func();
	};
	params.alert_func = params.alert_func || function() { };
	this.max_byte_error_color = "orangered";
	this.passwordChar = /[^0-9a-zA-Z\_]/g;
	this.checkedNames = new Array();
	var break_obj;

	this.init = function(){
		String.prototype.utf8bytelength = function(){ 
			try{
				var s = this;
				var b, i, c;
				for(b=i=0;c=s.charCodeAt(i++);b+=c>>16?4:c>>11?3:c>>7?2:1);
				return b
			}catch(ex){
				return this;
			}
		}

		String.prototype.euckrbytelength = function(){ 
			try{
				var s = this;
				var b, i, c;
				for(b=i=0;c=s.charCodeAt(i++);b+=c>>7?2:1);
				return b
			}catch(ex){
				return this;
			}
		}

		String.prototype.bytelength = function(){ 
			try{
				return this.euckrbytelength();
			}catch(ex){
				return this;
			}
		}

		var self = this;
		$(params.form).attr("novalidate", "");
		var ignore = $(params.form).attr("data-ignore-attr-name");
		var notobjstr = "";
		if(typeof ignore == 'string' && ignore.length > 0){
			notobjstr = ":not([data-ignore='" + ignore + "'])";
		}
		$("input[display-bytes]" + notobjstr + ", textarea[display-bytes]" + notobjstr, params.form).keyup(function(e){
			if($(this).attr("display-bytes") != ""){
				if($(this).attr("maxbytes") != null && $(this).val().bytelength() > parseInt($(this).attr("maxbytes"))){
					$("#" + $(this).attr("display-bytes")).html("<font style=\"color:" + self.max_byte_error_color + "\">" + $(this).val().bytelength() + "</font>");
					return;
				}else{
					$("#" + $(this).attr("display-bytes")).html($(this).val().bytelength());
				}
			}
		});
		$("input[display-bytes]" + notobjstr + ", textarea[display-bytes]" + notobjstr, params.form).trigger('keyup');
		$("input[numberonly]" + notobjstr + ", input[type='number']" + notobjstr, params.form).keyup(function(e){
			$(this).val( $(this).val().replace(/[^0-9]/g,"") );
		});
		$("input[numberbaronly]" + notobjstr , params.form).keyup(function(e){
			$(this).val( $(this).val().replace(/[^0-9-]/g,"") );
		});
		$("input[engnumonly]" + notobjstr, params.form).keyup(function(e){
			$(this).val( $(this).val().replace(/[^0-9a-zA-Z]/g,"") );
		});
	}
	this.clear = function(){
		this.checkedNames = new Array();
	}
	this.formValidate = function(once_alert){
		this.clear();
		var isValidatePass = true;
		var ignore = $(params.form).attr("data-ignore-attr-name");
		var notobjstr = ":not([novalidate])";
		if(typeof ignore == 'string' && ignore.length > 0){
			notobjstr = ":not([novalidate],[data-ignore='" + ignore + "'])";
		}
		var objs = $("input" + notobjstr + ", textarea" + notobjstr + ", select" + notobjstr, params.form);
		var self = this;
		$.each(objs, function(ikey, ival){
			if(!self.inputValidate(ival)){
				isValidatePass = false;
				if(once_alert != undefined){
					return false; // jquery break;
				}
			}
		});

		return isValidatePass;
	}

	this.getValue = function(selecter){
		this.clear();
		input_obj = $(selecter);
		if(input_obj.length <= 0){
			return false;
		}
		var tagname = input_obj.get(0).tagName.toLowerCase();
		switch(tagname){
			case "select":
				var selected_obj = $("option:selected", input_obj);
				return selected_obj.val();
			case "radio":
			case "checkbox":
				if($(":checked", input_obj).length > 0){
					return input_obj.val();
				}else{
					return false;
				}
		}
	}

	this.inputValidate = function(input_obj){
		var tagname = input_obj.tagName.toLowerCase();
		input_obj = $(input_obj);
		var msg = "";		
		switch(tagname){
			case "select":
				if(input_obj.attr("required") != undefined){
					var option_objs = $("option", input_obj);
					var selected_obj = $("option:selected", input_obj);
					if(option_objs.length > 0 && (selected_obj.length <= 0 || selected_obj.is('[value]') == false)){
						if(input_obj.attr("data-required-message") != undefined){
							msg = input_obj.attr("data-required-message") + "\n";
						}else{
							msg = input_obj.attr("title") + "은(는) 필수입력 입니다.\n";
						}
					}
				}
				break;
			case "textarea":
				if(input_obj.attr("required") != undefined){
					if(input_obj.val().length <= 0){
						if(input_obj.attr("data-required-message") != undefined){
							msg = input_obj.attr("data-required-message") + "\n";
						}else{
							msg = input_obj.attr("title") + "은(는) 필수입력 입니다.\n";
						}
					}
				}

				if(input_obj.attr("minlength") != undefined || input_obj.attr("maxlength") != undefined){
					var min = null;
					var max = null;
					if(input_obj.attr("minlength") != undefined){
						min = Number(input_obj.attr("minlength"));
					}					
					if(input_obj.attr("maxlength") != undefined){
						max = Number(input_obj.attr("maxlength"));
					}					
					if(min != null && max != null){
						if(!(input_obj.val().length >= min && input_obj.val().length <= max)){
							if(input_obj.attr("data-length-message") != undefined){
								msg = input_obj.attr("data-length-message") + "\n";
							}else{
								msg = input_obj.attr("title") + "은(는) " + min + "자이상 " + max + "자이하로 입력해야합니다\n";
							}
						}
					}else if(min != null){
						if(input_obj.val().length < min){
							if(input_obj.attr("data-length-message") != undefined){
								msg = input_obj.attr("data-length-message") + "\n";
							}else{
								msg = input_obj.attr("title") + "은(는) " + min + "자이상 입력해야합니다\n";
							}
						}
					}else if(max != null){
						if(input_obj.val().length > max){
							if(input_obj.attr("data-length-message") != undefined){
								msg = input_obj.attr("data-length-message") + "\n";
							}else{
								msg = input_obj.attr("title") + "은(는) " + max + "자이하 입력해야합니다\n";
							}
						}
					}
				}

				if(input_obj.attr("minbytes") != undefined || input_obj.attr("maxbytes") != undefined){
					var min = null;
					var max = null;

					if(input_obj.attr("minbytes") != undefined){
						min = Number(input_obj.attr("minbytes"));
					}					
					if(input_obj.attr("maxbytes") != undefined){
						max = Number(input_obj.attr("maxbytes"));
					}

					if(min != null && max != null){
						if(!(input_obj.val().bytelength() >= min && input_obj.val().bytelength() <= max)){
							if(input_obj.attr("data-bytes-message") != undefined){
								msg = input_obj.attr("data-bytes-message") + "\n";
							}else{
								msg = input_obj.attr("title") + "은(는) " + min + "bytes이상 " + max + "bytes이하로 입력해야합니다\n";
							}
						}
					}else if(min != null){
						if(input_obj.val().bytelength() < min){
							if(input_obj.attr("data-bytes-message") != undefined){
								msg = input_obj.attr("data-bytes-message") + "\n";
							}else{
								msg = input_obj.attr("title") + "은(는) " + min + "bytes이상 입력해야합니다\n";
							}
						}
					}else if(max != null){
						if(input_obj.val().bytelength() > max){
							if(input_obj.attr("data-bytes-message") != undefined){
								msg = input_obj.attr("data-bytes-message") + "\n";
							}else{
								msg = input_obj.attr("title") + "은(는) " + max + "bytes이하 입력해야합니다\n";
							}
						}
					}
				}
				break;
			case "input":
				if(input_obj.attr("required") != undefined){
					switch(input_obj.attr("type").toLowerCase()){
						case "radio":
						case "checkbox":
							if(this.checkedNames.indexOf(input_obj.attr("name")) < 0){
								if(input_obj.attr("name") == null){
									if(!input_obj.is(":checked")){
										if(input_obj.attr("data-required-message") != undefined){
											msg = input_obj.attr("data-required-message") + "\n";
										}else{
											msg = input_obj.attr("title") + "은(는) 필수선택 입니다.\n";
										}
									}
								}else if($("[name='" + input_obj.attr("title") + "']:checked", input_obj.closest("form")).length <= 0){
									this.checkedNames.push(input_obj.attr("name"));
									if(input_obj.attr("data-required-message") != undefined){
										msg = input_obj.attr("data-required-message") + "\n";
									}else{
										msg = input_obj.attr("title") + "은(는) 필수선택 입니다.\n";
									}
								}
							}
							break_obj = input_obj;
							break;
						case "button":
						case "submit":
							break;
						case "text":
						case "password":
						case "email":
						case "number":
							var check_is = true;

							if(input_obj.val().length <= 0 && check_is == true){
								if(input_obj.attr("data-required-message") != undefined){
									msg = input_obj.attr("data-required-message") + "\n";
								}else{
									msg = input_obj.attr("title") + "은(는) 필수입력 입니다.\n";
								}
								check_is = false;
							}
							if((input_obj.attr("minlength") != undefined || input_obj.attr("maxlength") != undefined) && check_is == true){
								var min = null;
								var max = null;

								if(input_obj.attr("minlength") != undefined) min = Number(input_obj.attr("minlength"));
								if(input_obj.attr("maxlength") != undefined) max = Number(input_obj.attr("maxlength"));
								
								if(min != null && max != null){
									if(!(input_obj.val().length >= min && input_obj.val().length <= max)){
										if(input_obj.attr("data-length-message") != undefined){
											msg = input_obj.attr("data-length-message") + "\n";
										}else{
											msg = input_obj.attr("title") + "은(는) " + min + "자이상 " + max + "자이하로 입력해야합니다\n";
										}
										check_is = false;
									}
								}else if(min != null){
									if(input_obj.val().length < min){
										if(input_obj.attr("data-length-message") != undefined){
											msg = input_obj.attr("data-length-message") + "\n";
										}else{
											msg = input_obj.attr("title") + "은(는) " + min + "자이상 입력해야합니다\n";
										}
										check_is = false;
									}
								}else if(max != null){
									if(input_obj.val().length > max){
										if(input_obj.attr("data-length-message") != undefined){
											msg = input_obj.attr("data-length-message") + "\n";
										}else{
											msg = input_obj.attr("title") + "은(는) " + max + "자이하 입력해야합니다\n";
										}
										check_is = false;
									}
								}
							}

							if((input_obj.attr("minbytes") != undefined || input_obj.attr("maxbytes") != undefined) && check_is == true){
								var min = null;
								var max = null;

								if(input_obj.attr("minbytes") != undefined) min = Number(input_obj.attr("minbytes"));
								if(input_obj.attr("maxbytes") != undefined) max = Number(input_obj.attr("maxbytes"));

								if(min != null && max != null){
									if(!(input_obj.val().bytelength() >= min && input_obj.val().bytelength() <= max)){
										if(input_obj.attr("data-bytes-message") != undefined){
											msg = input_obj.attr("data-bytes-message") + "\n";
										}else{
											msg = input_obj.attr("title") + "은(는) " + min + "bytes이상 " + max + "bytes이하로 입력해야합니다\n";
										}
										check_is = false;
									}
								}else if(min != null){
									if(input_obj.val().bytelength() < min){
										if(input_obj.attr("data-bytes-message") != undefined){
											msg = input_obj.attr("data-bytes-message") + "\n";
										}else{
											msg = input_obj.attr("title") + "은(는) " + min + "bytes이상 입력해야합니다\n";
										}
										check_is = false;
									}
								}else if(max != null){
									if(input_obj.val().bytelength() > max){
										if(input_obj.attr("data-bytes-message") != undefined){
											msg = input_obj.attr("data-bytes-message") + "\n";
										}else{
											msg = input_obj.attr("title") + "은(는) " + max + "bytes이하 입력해야합니다\n";
										}
										check_is = false;
									}
								}
							}

							var input_type = input_obj.attr("type").toLowerCase();
							if(input_type == "email" && check_is == true){
								var regExp = /^[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_\.]?[0-9a-zA-Z])*\.[a-zA-Z]{2,4}$/i; 
								if(input_obj.val().length > 0 && !regExp.test(input_obj.val())){
									if(input_obj.attr("data-email-message") != undefined){
										msg = input_obj.attr("data-email-message") + "\n";
									}else{
										msg = input_obj.attr("title") + "은(는) 이메일형식이 올바르지 않습니다\n";
									}
									check_is = false;
								}
							}else if((input_type == "date" || input_obj.attr("datepicker") != undefined) && check_is == true){
								var regExp = /^[0-9]{4}\-{1}[0-9]{2}\-{1}[0-9]{2}$/; 
								if(input_obj.val().length > 0 && !regExp.test(input_obj.val())){
									if(input_obj.attr("data-date-message") != undefined){
										msg = input_obj.attr("data-date-message") + "\n";
									}else{
										msg = input_obj.attr("title") + "은(는) 날짜 형식에 어긋납니다. 다시 입력해주세요.\n";
									}
									check_is = false;
								}
							}else if((input_type == "number" || input_obj.attr("numberonly") != undefined) && check_is == true){
								if(input_obj.val().length > 0 && $.isNumeric(input_obj.val()) == false){
									if(input_obj.attr("data-number-message") != undefined){
										msg = input_obj.attr("data-number-message") + "\n";
									}else{
										msg = input_obj.attr("title") + "은(는) 숫자만 입력해주세요.\n";
									}
									check_is = false;
								}
							}
							if(input_obj.attr("equalto") != undefined && check_is == true){
								if($(input_obj.attr("equalto")).length > 0){
									var equalto_e = $(input_obj.attr("equalto"));
									if(equalto_e.val() != input_obj.val()){
										if(input_obj.attr("data-equalto-message") != undefined){
											msg = input_obj.attr("data-equalto-message") + "\n";
										}else{
											msg = input_obj.attr("title") + "와 " + equalto_e.attr("name") + "의 값이 일치하지 않습니다.\n";
										}
										check_is = false;
									}
								}
							}

							if(input_obj.attr("contain") != undefined && check_is == true){
								if(input_obj.attr("contain").length > 0){
									var regexps = input_obj.attr("contain").split(",");
									for(var i=0; i < regexps.length; i++){
										var regExp = new RegExp(regexps[i]);
										if(!regExp.test(input_obj.val())){
											if(input_obj.attr("data-contain-message") != undefined){
												msg = input_obj.attr("data-contain-message") + "\n";
											}else{
												msg = input_obj.attr("title") + "의 잘못된 입력을 하였습니다.\n";
											}
											check_is = false;
											break;
										}
									}
								}
							}

							if(input_obj.attr("regex") != undefined && check_is == true){
								var regExp = new RegExp(input_obj.attr("regex"));
								if(!regExp.test(input_obj.val())){
									if(input_obj.attr("data-regex-message") != undefined){
										msg = input_obj.attr("data-regex-message") + "\n";
									}else{
										msg = input_obj.attr("title") + "의 잘못된 입력을 하였습니다.\n";
									}
									check_is = false;
								}
							}

							if(msg.length > 0) break_obj = input_obj;
							break;
						default:
							if(input_obj.val().length <= 0){
								if(input_obj.attr("data-required-message") != undefined){
									msg = input_obj.attr("data-required-message") + "\n";
								}else{
									msg = input_obj.attr("title") + "은(는) 필수입력 입니다.\n";
								}
							}
							break_obj = input_obj;
							break;
					}
				}
				break;
		}
		if(msg.length > 0){
			if(typeof params.alert == "function"){
				if(typeof break_obj != "" && break_obj != undefined) break_obj.focus();
				params.alert(msg, break_obj);
			}
			return false;
		}else{
			return true;
		}
		return false;
	}
	this.construct = this.init();
}