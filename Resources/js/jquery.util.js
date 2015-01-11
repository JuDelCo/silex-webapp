/*!
 * jQuery Util JavaScript Library v1.0.8
 *
 * Author: Juan Delgado Cobalea
 *
 * Last-Update: 2014-06-17
 */

(function($)
{
	$.fn.visible = function(action)
	{
		if(action === "show")
		{
			this.removeClass('hidden');
		}
		else if(action === "hide")
		{
			this.addClass('hidden');
		}
		else if(action === "toggle")
		{
			this.toggleClass('hidden');
		}

		return this;
	};

	$.extend(
	{
		// JQUERY NATIVE:
		// 		$.isArray(object)
		// 		$.trim(string)

		// TODO:
		// 		$.isDateTime( value, format )

		log: function(argument)
		{
			if(window['console'] !== undefined)
			{
				console.log(argument);
			}
		},
		getDefault: function(argument, default_value)
		{
			return (typeof argument == 'undefined' ? default_value : argument);
		},
		isInArray: function(value, array)
		{
			if($.inArray(value, array) > -1)
			{
				return true;
			}

			return false;
		},
		count: function(array)
		{
			if (array === null || typeof array === 'undefined')
			{
				return 0;
			}
			else if (array.constructor !== Array && array.constructor !== Object)
			{
				return 1;
			}

			return array.length;
		},
		regEx: function(string, pattern, modifiers)
		{
			modifiers = $.getDefault(modifiers, ''); // i (case-insensitive), g (global match), m (multiline matching)

			if(typeof string == 'string')
			{
				var regex_pattern = new RegExp(pattern, modifiers);

				return regex_pattern.test(string);
			}

			return false;
		},
		getNumberFromString: function(value)
		{
			return parseFloat($.trim(value).replace(/,/g, ''));
		},
		clamp: function(number, min, max)
		{
			if (isNaN(number.value))
			{
				number.value = 0;
			}

			if($.isEmpty(number))
			{
				number.value = 0;
			}

			if($.isDecimal(number))
			{
				number = parseFloat(number.value);
			}
			else
			{
				number = parseInt(number.value);
			}

			if (number < min)
			{
				number.value = min;
			}
			else if (number > max)
			{
				number.value = max;
			}
			else
			{
				number.value = number;
			}

			return number;
		},
		isInteger: function(value)
		{
			return value === +value && isFinite(value) && !(value % 1);
		},
		isDecimal: function(value)
		{
			return +value === value && (!isFinite(value) || !!(value % 1));
		},
		isNumeric: function(value)
		{
			if($.isString(value))
			{
				value = $.getNumberFromString(value);
			}

			if($.isInteger(value) || $.isDecimal(value))
			{
				return true;
			}

			return false;
		},
		isString: function(value)
		{
			return (typeof value === 'string');
		},
		isBool: function(value)
		{
			if(value === true || value === false)
			{
				return true;
			}

			return false;
		},
		isEmpty: function(value, trim)
		{
			trim = $.getDefault(trim, false);

			if(trim && $.isString(value))
			{
				value = $.trim(value);
			}

			var undef, key, i, len;
			var emptyValues = [undef, null, false, 0, "", "0"];

			for (i = 0, len = emptyValues.length; i < len; i++)
			{
				if (value === emptyValues[i])
				{
					return true;
				}
			}

			if (typeof value === "object")
			{
				for (key in value)
				{
					return false;
				}

				return true;
			}

			return false;
		},
		isNotEmpty: function(value, trim)
		{
			trim = $.getDefault(trim, false);

			return !$.isEmpty(value, trim);
		},
		isNull: function(value)
		{
			return (value === null);
		},
		isNotNull: function(value)
		{
			return !$.isNull(value);
		},
		isEmail: function(value)
		{
			if($.isString(value) && $.isNotEmpty(value))
			{
				var email_regex  = '^([0-9a-zA-Z]([-.\\w]*[0-9a-zA-Z])*@([0-9a-zA-Z][-\\w]*[0-9a-zA-Z]\\.)+[a-zA-Z]{2,9})$';

				return $.regEx(value, email_regex);
			}

			return false;
		},
		isDate: function(value)
		{
			if(value == '' || !$.isString(value))
			{
				return false;
			}

			var date_regex_pattern = /^(\d{1,2})(\/|-)(\d{1,2})(\/|-)(\d{4})$/;
			var result_array = value.match(date_regex_pattern);

			if (result_array == null)
			{
				return false;
			}

			// Date Format: dd/mm/yyyy
			day = result_array[1];
			month = result_array[3];
			year = result_array[5];
			if (month < 1 || month > 12)
			{
				return false;
			}
			else if (day < 1 || day > 31)
			{
				return false;
			}
			else if ((month == 4 || month == 6 || month == 9 || month == 11) && day == 31)
			{
				return false;
			}
			else if (month == 2)
			{
				var isLeap = (year % 4 == 0 && (year % 100 != 0 || year % 400 == 0));

				if (day > 29 || (day == 29 && !isLeap))
				{
					return false;
				}
			}

			return true;
		},
		isAlphaNumeric: function(value, allowSpaces, allowUnderscores)
		{
			allowSpaces = $.getDefault(allowSpaces, true);
			allowUnderscores = $.getDefault(allowUnderscores, true);

			var regex = '^[a-zA-Z0-9' + (allowSpaces ? ' ' : '') + (allowUnderscores ? '_' : '') + ']*$';

			return $.regEx(value, regex);
		},
		isJson: function(value)
		{
			try
			{
				var json = $.parseJSON(value);
			}
			catch(error)
			{
				return false;
			}

			return !(json === null);
		},
		strFileSize: function(value, decimals, showBytes)
		{
			decimals = $.getDefault(decimals, 2);
			showBytes = $.getDefault(showBytes, true);

			var string = '';
			var size = parseInt(value);

			var kilobyte = 1024;
			var megabyte = kilobyte * 1024;
			var gigabyte = megabyte * 1024;

			if(isNaN(size))
			{
				string = 'Error';
			}
			else if(size < kilobyte)
			{
				if(showBytes)
				{
					string = '' + size + ' bytes';
				}
				else
				{
					string = '' + (size / kilobyte).toFixed(decimals) + ' KB';
				}
			}
			else if(size < megabyte)
			{
				string = '' + (size / kilobyte).toFixed(decimals) + ' KB';
			}
			else if(size < gigabyte)
			{
				string = '' + (size / megabyte).toFixed(decimals) + ' MB';
			}
			else
			{
				string = '' + (size / gigabyte).toFixed(decimals) + ' GB';
			}

			return string;
		},
		onAjaxError: function(jqXHR, callback)
		{
			callback = $.getDefault(callback, function(message) { alert(message); });

			var default_msg = 'Ha ocurrido un error al realizar la petición al servidor';

			if(jqXHR.status == 0 || jqXHR.statusText == 'abort' || !jqXHR.getAllResponseHeaders()) // User Abort
			{
				return false;
			}

			if(jqXHR.status != 500)
			{
				var data;

				if($.isJson(jqXHR.responseText))
				{
					data = $.parseJSON(jqXHR.responseText);
				}
				else
				{
					data = {'error': default_msg};
				}

				if($.isNotNull(data) && $.isNotEmpty(data['error']))
				{
					callback(data.error);

					return true;
				}
			}

			callback(default_msg);

			return true;
		},
		modalWorking: function(value)
		{
			$('#modal_working').modal(value);
		}
	});

	// Scroll-to-top button show and hide
	$(window).scroll(function()
	{
		if ($(this).scrollTop() > 100)
		{
			$('.scrollup').fadeIn();
		}
		else
		{
			$('.scrollup').fadeOut();
		}
	});

	// Scroll-to-top animate
	$('.scrollup').click(function()
	{
		$("html, body").animate({ scrollTop: 0 }, 600);

		return false;
	});
}(jQuery));
