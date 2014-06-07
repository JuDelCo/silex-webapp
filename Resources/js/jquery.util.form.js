/*!
 * jQuery Util Form JavaScript Library v1.0.2
 *
 * Author: Juan Delgado Cobalea
 *
 * Last-Update: 2014-06-04
 */

(function($)
{
	$.utilForm = (function()
	{
		var options = {
			ajax_api: ''
		};

		var app =
		{
			init: function(new_options)
			{
				options = $.extend(options, new_options);
			},
			ajax_send_data: function(path, obj_search, callbacks)
			{
				var callbacks = $.extend(
				{
					'validation_fail': $.noop,
					'before': $.noop,
					'done': $.noop,
					'fail': $.noop,
					'always': $.noop
				}, callbacks);

				if($.isEmpty(path))
				{
					callbacks.validation_fail('No se ha especificado la ruta (url) ajax');
					return;
				}

				if($.active > 0) // Variable interna de jQuery: Indica el número de conexiones activas (ajax)
				{
					callbacks.validation_fail('Espera a que terminen de cargarse todos los campos');

					return;
				}

				var data = app.get_fields_info(obj_search);

				for (var id in data)
				{
					if (! data.hasOwnProperty(id))
					{
						continue;
					}

					if(data[id].state == 'obligatorio')
					{
						if($.isEmpty(data[id].value))
						{
							callbacks.validation_fail('', id, data[id].tab);
							return;
						}
					}
				}

				callbacks.before();

				$.ajax(
				{
					type: "POST",
					url: path,
					contentType : "application/json",
					data: JSON.stringify(
					{
						data: data,
						random: encodeURI((Math.random()*99999))
					})
				}
				).done(function(data)
				{
					callbacks.done(data);
				}
				).fail(function(jqXHR)
				{
					if($.onAjaxError(jqXHR))
					{
						callbacks.fail();
					}
				}
				).always(function()
				{
					callbacks.always();
				});
			},
			set_field_value: function(obj_id, data, autoselect_single_value)
			{
				var element = $('#' + obj_id);

				// Si no se encontró la ID, debe de ser un input:radio (o bien no existe el elemento)
				if(!element)
				{
					if($('input:radio[name="' + obj_id + '"]').length > 0)
					{
						return;
					}
					else
					{
						if(! data.data[0].value)
						{
							$('input:radio[name="' + obj_id + '"]').prop('checked', false);
						}
						else
						{
							$('input:radio[name="' + obj_id + '"]').filter('[value="' + data.data[0].value + '"]').prop('checked', true);
						}
					}

					return;
				}

				switch($(element).prop('type'))
				{
					case 'select-one':
					case 'select-multiple':
						var old_data = $(element).data('old_value');
						var old_value = old_data.value;
						var old_text = old_data.text;

						$(element).removeData('old_value');
						$(element).html(data.html_options);

						// Añadimos el valor que poseía antes (el option) si 'preserve_value' == true
						if(old_value != 0 && data.preserve_value && $(element).find('option[value='+ old_value +']').length == 0)
						{
							$(element).append('<option value="' + old_value + '">' + old_text + '</option>');
						}

						// Si es un select con un solo valor... (sin contar el "seleccione")
						if($(element).find('option').not('[value=0]').length == 1)
						{
							if(autoselect_single_value || old_value == $(element).find('option').not('[value=0]').val())
							{
								$(element).val($(element).find('option').not('[value=0]').val());
							}

							if(old_value != $(element).val())
							{
								$(element).trigger('change');
							}
						}
						else
						{
							if(old_value != 0)
							{
								if($(element).find('option[value='+ old_value +']').length)
								{
									$(element).val(old_value);
								}
								else
								{
									$(element).trigger('change');
								}
							}
						}

						// Deshabilitamos los options si el select está en modo readonly
						if($(element).attr('readonly'))
						{
							$(element).find('option').prop('disabled', true);
						}

						break;
					case 'text':
					case 'hidden':
					case 'textarea':
						$(element).val(data.data[0].value);

						break;
					case 'checkbox':
						$(element).prop('checked', (data.data[0].value ? true : false));

						break;
					default:
						return;
				}
			},
			reset_field_value: function(obj_id)
			{
				var element = $('#' + obj_id);

				// Si no se encontró la ID, debe de ser un input:radio (o bien no existe el elemento)
				if(!element)
				{
					if($('input:radio[name="' + obj_id + '"]').length > 0)
					{
						$('input:radio[name="' + obj_id + '"]').prop('checked', false);
					}

					return;
				}

				switch($(element).prop('type'))
				{
					case 'select-one':
					case 'select-multiple':
						$(element).html('<option value="0"></option>');

						break;
					case 'text':
					case 'hidden':
					case 'textarea':
						$(element).val('');

						break;
					case 'checkbox':
						$(element).prop('checked', false);

						break;
				}

				var xhr = $(element).data('xhr');

				// Abortamos la conexión ajax si existe
				if(xhr)
				{
					xhr.abort();
				}
			},
			set_field_ajax_state: function(obj_id, enabled)
			{
				var element = $('#' + obj_id);

				// Comprobamos que sea un select o un campo de texto
				if(! $(element).is('select') && ! $(element).is('input:text'))
				{
					return;
				}

				var is_enabled = false;

				// Revisamos si ya está en estado "cargando" o no
				if($(element).next().hasClass('ajax-loading-input') || $(element).next().hasClass('ajax-loading-select'))
				{
					is_enabled = true;
				}

				if(enabled)
				{
					if(! is_enabled)
					{
						$(element).after('<div class="ajax-loading-' + ($(element).is('select') ? 'select' : 'input') + '"></div>');
					}
				}
				else
				{
					if(is_enabled)
					{
						$(element).next().remove();
					}
				}
			},
			ajax_get_field_value: function(obj_id, request_id, parametros, callbacks)
			{
				var parametros = $.extend(
				{
					'preserve_value': false,
					'request_type': 'options',
					'request_filters': {},
					'request_options': {
						'default_append': true,
						'default_id': '0',
						'default_value': 'Selecciona'
					}
				}, parametros);

				var callbacks = $.extend(
				{
					'done': $.noop,
					'fail': $.noop,
					'always': $.noop
				}, callbacks);

				if($.isEmpty(obj_id) || $.isEmpty(request_id))
				{
					return;
				}

				var element = $('#' + obj_id);

				// Comprobamos que sea un input, select o textarea (campos de formulario html)
				if(! $(element).is('input') && ! $(element).is('select') && ! $(element).is('textarea'))
				{
					return;
				}

				for (var filter in parametros.request_filters)
				{
					if(! parametros.request_filters.hasOwnProperty(filter))
					{
						continue;
					}

					// En caso de que un filtro tenga un valor nulo o igual a cero, detenemos el refresco y reseteamos el campo
					if($.isEmpty(parametros.request_filters[filter], true))
					{
						app.reset_field_value(obj_id);

						if($(element).is('select'))
						{
							$(element).trigger('change');
						}

						return;
					}
				}

				var xhr = $(element).data('xhr');

				// Abortamos la conexión ajax si existe
				if(xhr)
				{
					xhr.abort();
				}
				else
				{
					if($(element).is('select'))
					{
						$(element).data('old_value', {
							'value': $(element).val(),
							'text': $(element).find('option:selected').html()
						});
					}
				}

				app.reset_field_value(obj_id);

				if($.isEmpty(options.ajax_api))
				{
					return;
				}

				app.set_field_ajax_state(obj_id, true);

				xhr = $.ajax(
				{
					type: "POST",
					url: options.ajax_api,
					contentType : "application/json",
					data: JSON.stringify(
					{
						request_id: request_id,
						request_type: parametros.request_type,
						request_filters: parametros.request_filters,
						request_options: parametros.request_options,
						random: encodeURI((Math.random()*99999))
					})
				}
				).done(function(data)
				{
					data.preserve_value = parametros.preserve_value;

					callbacks.done(obj_id, data);
				}
				).fail(function(jqXHR)
				{
					if($.onAjaxError(jqXHR))
					{
						callbacks.fail(obj_id);
					}
				}
				).always(function()
				{
					callbacks.always(obj_id);

					// Eliminamos la petición Ajax del elemento (DOM)
					$(element).removeData('xhr');

					app.set_field_ajax_state(obj_id, false);
				});

				// Guardamos la peticion Ajax en el elemento (DOM)
				$(element).data('xhr', xhr);
			},
			ajax_set_field_value: function(obj_id, request_id, parametros, callbacks)
			{
				var parametros = $.extend({ 'autoselect_single_value': true }, parametros);

				var callbacks = $.extend(
				{
					'done': $.noop,
					'fail': $.noop,
					'always': $.noop
				}, callbacks);

				// Igual que llamar directamente a esta función solo que se actualiza el campo sin tener que pasarle un callback para ello
				app.ajax_get_field_value(obj_id, request_id, parametros,
				{
					'done': function(obj_id, data)
					{
						app.set_field_value(obj_id, data, parametros.autoselect_single_value);
						callbacks.done(obj_id, data);
					},
					'fail': callbacks.fail,
					'always': callbacks.always
				});
			},
			set_fields_state: function(array_campos_estado)
			{
				for (var obj_id in array_campos_estado)
				{
					if (! array_campos_estado.hasOwnProperty(obj_id))
					{
						continue;
					}

					var element = $('#' + obj_id);
					var es_radio = false;

					// Si no se encontró la ID, debe de ser un input:radio (o bien no existe el elemento)
					if($(element).length == 0)
					{
						element = $('[name="' + obj_id + '"]');

						if($(element).attr('type') == 'radio')
						{
							es_radio = true;
						}
						else
						{
							// No existe u es otro elemento desconocido
							continue;
						}
					}

					// "Limpiamos" el campo y lo desbloqueamos
					$(element).removeClass('obligatorio').attr('readonly', false).prop('disabled', false);
					$(element).removeAttr("readonly"); // Fix IE8 Bug

					if(es_radio)
					{
						$(element).parent().parent('div.form-control').removeClass('obligatorio');
					}

					// Y si es un select, hacemos lo mismo con sus options
					if($(element).is("select"))
					{
						$(element).find('option').prop('disabled', false);
					}

					// Configuramos el campo dependiendo del estado que se desee
					if(array_campos_estado[obj_id].state == 'obligatorio')
					{
						if(es_radio)
						{
							$(element).parent().parent('div.form-control').addClass('obligatorio');
						}
						else
						{
							$(element).addClass('obligatorio');
						}
					}
					else if(array_campos_estado[obj_id].state == 'habilitado')
					{
						// Lo dejamos como está, no es necesario modificarlo más
					}
					else // Lo deshabilitamos si es otro tipo
					{
						if($(element).is(":checkbox") || $(element).is(":radio"))
						{
							$(element).prop('disabled', true);
						}
						else
						{
							$(element).attr('readonly', true);

							if($(element).is("select"))
							{
								$(element).find('option').prop('disabled', true);
							}
						}
					}
				}
			},
			get_fields_info: function(obj_search, obj_id)
			{
				// Elemento o elementos contenedores de campos de formulario HTML (input, select, textarea, ...)
				var obj_search = $.extend($('html > body'), obj_search);
				var array = {};

				// Obtenemos los elementos input, select y textarea que no sean de tipo radio
				$(obj_search).find('input:not([readonly]):not(:radio):enabled, select:not([readonly]), textarea:not([readonly])').each(function()
				{
					var obj = {
						'value': $(this).val(),
						'element': $(this).prop('tagName').toLowerCase(),
						'tab': $(this).parents('div.tab-pane').attr('id')
					};

					if($(this).attr('type'))
					{
						obj.type = $(this).attr('type');

						if(obj.type == "checkbox")
						{
							obj.value = ($(this).is(':checked') ? true : false);
						}
					}

					if($(this).hasClass('obligatorio'))
					{
						obj.state = "obligatorio";
					}
					else
					{
						obj.state = "habilitado";
					}

					array[$(this).attr('id')] = obj;
				});

				var array_radio = [];

				// Generamos un array único con los nombres (grupos) de los input de tipo radio existentes
				$(obj_search).find('input:radio:enabled').each(function()
				{
					var found = false;

					for (var id = array_radio.length - 1; id >= 0; id--)
					{
						if(array_radio[id].id == $(this).attr('name'))
						{
							found = true;
						}
					};

					if(! found)
					{
						var state = "";

						if($(this).parent().parent().hasClass('obligatorio'))
						{
							state = "obligatorio";
						}
						else
						{
							state = "habilitado";
						}

						array_radio.push({
							'id': $(this).attr('name'),
							'element': $(this).prop('tagName').toLowerCase(),
							'tab': $(this).parents('div.tab-pane').attr('id'),
							'type': 'radio', 'state': state
						});
					}
				});

				// Para cada nombre (grupo de radio buttons) encontrado, comprobamos su valor
				for (var id = array_radio.length - 1; id >= 0; id--)
				{
					var element = $('input:radio[name="' + array_radio[id].id + '"]:checked');

					if($(element).length)
					{
						array_radio[id].value = $(element).val();
					}
				};

				// Unificamos ambos arrays
				for (var id = array_radio.length - 1; id >= 0; id--)
				{
					var temp_id = array_radio[id].id;

					delete array_radio[id].id;

					array[temp_id] = array_radio[id];
				};

				// Si se ha solicitado un objeto en concreto...
				if(obj_id)
				{
					for (var id in array)
					{
						if (! array.hasOwnProperty(id))
						{
							continue;
						}

						if(array[id] && id == obj_id)
						{
							return array[id];
						}
					}

					return {};
				}

				return array;
			}
		};

		return app;
	})(); // Singleton
})(jQuery);
