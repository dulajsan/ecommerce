/**
 * Created by rob on 22/03/2016.
 */
define(['jquery'], function (jQuery) {

    var adminFilters = new Class({

        Implements: [Options],

        options: {
            j3: false
        },

        initialize: function (el, fields, options) {
            this.el = document.id(el);
            this.fields = fields;
            this.setOptions(options);
            this.filters = [];
            this.counter = 0;
        },

        addHeadings: function () {
            var thead = new Element('thead').adopt(new Element('tr', {'id': 'filterTh', 'class': 'title'}).adopt(
                new Element('th').set('text', Joomla.JText._('COM_FABRIK_JOIN')),
                new Element('th').set('text', Joomla.JText._('COM_FABRIK_FIELD')),
                new Element('th').set('text', Joomla.JText._('COM_FABRIK_CONDITION')),
                new Element('th').set('text', Joomla.JText._('COM_FABRIK_VALUE')),
                new Element('th').set('text', Joomla.JText._('COM_FABRIK_TYPE')),
                new Element('th').set('text', Joomla.JText._('COM_FABRIK_APPLY_FILTER_TO')),
                new Element('th').set('text', Joomla.JText._('COM_FABRIK_GROUPED')),
                new Element('th').set('text', Joomla.JText._('COM_FABRIK_DELETE'))
            ));
            thead.inject(document.id('filterContainer'), 'before');
        },

        deleteFilterOption: function (e) {
            this.counter--;
            var tbl, t;
            e.stop();
            if (this.options.j3) {
                var row = e.target.id.replace('filterContainer-del-', '').toInt();

                t = e.target.getParent('tr');
                tbl = e.target.getParent('table');
            } else {
                //t = document.id(element.parentNode.parentNode); //was 3 but that was the tbody
                t = e.target.getParent('tr');
                tbl = e.target.getParent('table');
            }

            if (this.counter === 0) {
                tbl.hide();
            }

            if (this.options.j3) {
                // in 3.1 we have to hide the rows rather than destroy otherwise the form doesn't submit!!!
                t.getElements('input, select, textarea').dispose();
                t.hide();
            } else {
                t.dispose();
            }
        },

        _makeSel: function (c, name, pairs, sel, showSelect) {
            var opts = [];
            showSelect = showSelect === true ? true : false;
            if (showSelect) {
                opts.push(new Element('option', {'value': ''}).set('text', Joomla.JText._('COM_FABRIK_PLEASE_SELECT')));
            }
            pairs.each(function (pair) {
                if (pair.value === sel) {
                    opts.push(new Element('option', {
                        'value'   : pair.value,
                        'selected': 'selected'
                    }).set('text', pair.label));
                } else {
                    opts.push(new Element('option', {'value': pair.value}).set('text', pair.label));
                }
            });
            return new Element('select', {'class': c + ' input-medium', 'name': name}).adopt(opts);
        },

        addFilterOption: function (selJoin, selFilter, selCondition, selValue, selAccess, evaluate, grouped) {
            var and, or, joinDd, groupedNo, groupedYes, i, sels;
            if (this.counter <= 0) {
                if (this.el.getParent('table').getElement('thead')) {
                    // We've already added the thead - in 3.1 we have to hide the rows rather than destroy otherwise the form doesn't submit!!!
                } else {
                    this.addHeadings();
                }
            }
            selJoin = selJoin ? selJoin : '';
            selFilter = selFilter ? selFilter : '';
            selCondition = selCondition ? selCondition : '';
            selValue = selValue ? selValue : '';
            selAccess = selAccess ? selAccess : '';
            grouped = grouped ? grouped : '';
            var conditionsDd = this.options.filterCondDd;
            var tr = new Element('tr');
            if (this.counter > 0) {
                var opts = {
                    'type' : 'radio',
                    'name' : 'jform[params][filter-grouped][' + this.counter + ']',
                    'value': '1'
                };
                opts.checked = (grouped === '1') ? 'checked' : '';
                groupedYes = new Element('label').set('text', Joomla.JText._('JYES')).adopt(
                    new Element('input', opts)
                );
                // Need to redeclare opts for ie8 otherwise it renders a field!
                opts = {
                    'type' : 'radio',
                    'name' : 'jform[params][filter-grouped][' + this.counter + ']',
                    'value': '0'
                };
                opts.checked = (grouped !== '1') ? 'checked' : '';

                groupedNo = new Element('label').set('text', Joomla.JText._('JNO')).adopt(
                    new Element('input', opts)
                );

            }
            if (this.counter === 0) {
                joinDd = new Element('span').set('text', 'WHERE').adopt(
                    new Element('input', {
                        'type' : 'hidden',
                        'id'   : 'paramsfilter-join',
                        'class': 'inputbox',
                        'name' : 'jform[params][filter-join][]',
                        'value': selJoin
                    }));
            } else {
                if (selJoin === 'AND') {
                    and = new Element('option', {'value': 'AND', 'selected': 'selected'}).set('text', 'AND');
                    or = new Element('option', {'value': 'OR'}).set('text', 'OR');
                } else {
                    and = new Element('option', {'value': 'AND'}).set('text', 'AND');
                    or = new Element('option', {'value': 'OR', 'selected': 'selected'}).set('text', 'OR');
                }
                joinDd = new Element('select', {
                    'id'   : 'paramsfilter-join',
                    'class': 'inputbox  input-medium',
                    'name' : 'jform[params][filter-join][]'
                }).adopt(
                    [and, or]);
            }

            var tdGrouped = new Element('td');
            var td = new Element('td');

            if (this.counter <= 0) {
                tdGrouped.appendChild(new Element('input', {
                    'type' : 'hidden',
                    'name' : 'jform[params][filter-grouped][' + this.counter + ']',
                    'value': '0'
                }));
                tdGrouped.appendChild(new Element('span').set('text', 'n/a'));

            } else {
                tdGrouped.appendChild(groupedNo);
                tdGrouped.appendChild(groupedYes);
            }
            td.appendChild(joinDd);

            var td1 = new Element('td');
            td1.innerHTML = this.fields;
            var td2 = new Element('td');
            td2.innerHTML = conditionsDd;
            var td3 = new Element('td');
            var td4 = new Element('td');
            td4.innerHTML = this.options.filterAccess;
            var td5 = new Element('td');

            var textArea = new Element('textarea', {
                'name': 'jform[params][filter-value][]',
                'cols': 17,
                'rows': 4
            }).set('text', selValue);
            td3.appendChild(textArea);
            td3.appendChild(new Element('br'));

            var evalopts = [
                {'value': 0, 'label': Joomla.JText._('COM_FABRIK_TEXT')},
                {'value': 1, 'label': Joomla.JText._('COM_FABRIK_EVAL')},
                {'value': 2, 'label': Joomla.JText._('COM_FABRIK_QUERY')},
                {'value': 3, 'label': Joomla.JText._('COM_FABRIK_NO_QUOTES')}
            ];

            var tdType = new Element('td')
                .adopt(this._makeSel('inputbox elementtype', 'jform[params][filter-eval][]', evalopts, evaluate, false));

            var checked = (selJoin !== '' || selFilter !== '' || selCondition !== '' || selValue !== '') ? true : false;
            var delId = this.el.id + "-del-" + this.counter;

            var deleteText = this.options.j3 ? '' : Joomla.JText._('COM_FABRIK_DELETE');
            var bClass = this.options.j3 ? 'btn btn-danger' : 'removeButton';
            var a = '<button id="' + delId + '" class="' + bClass + '"><i class="icon-minus"></i> ' +
                deleteText + '</button>';
            td5.set('html', a);
            tr.appendChild(td);

            tr.appendChild(td1);
            tr.appendChild(td2);
            tr.appendChild(td3);
            tr.appendChild(tdType);
            tr.appendChild(td4);
            tr.appendChild(tdGrouped);
            tr.appendChild(td5);

            this.el.appendChild(tr);

            this.el.getParent('table').show();
            document.id(delId).addEvent('click', function (e) {
                this.deleteFilterOption(e);
            }.bind(this));

            document.id(this.el.id + '-del-' + this.counter).click = function (e) {
                this.deleteFilterOption(e);
            }.bind(this);

            /*set default values*/
            if (selJoin !== '') {
                sels = Array.from(td.getElementsByTagName('SELECT'));
                if (sels.length >= 1) {
                    for (i = 0; i < sels[0].length; i++) {
                        if (sels[0][i].value === selJoin) {
                            sels[0].options.selectedIndex = i;
                        }
                    }
                }
            }
            if (selFilter !== '') {
                sels = Array.from(td1.getElementsByTagName('SELECT'));
                if (sels.length >= 1) {
                    for (i = 0; i < sels[0].length; i++) {
                        if (sels[0][i].value === selFilter) {
                            sels[0].options.selectedIndex = i;
                        }
                    }
                }
            }

            if (selCondition !== '') {
                sels = Array.from(td2.getElementsByTagName('SELECT'));
                if (sels.length >= 1) {
                    for (i = 0; i < sels[0].length; i++) {
                        if (sels[0][i].value === selCondition) {
                            sels[0].options.selectedIndex = i;
                        }
                    }
                }
            }

            if (selAccess !== '') {
                sels = Array.from(td4.getElementsByTagName('SELECT'));
                if (sels.length >= 1) {
                    for (i = 0; i < sels[0].length; i++) {
                        if (sels[0][i].value === selAccess) {
                            sels[0].options.selectedIndex = i;
                        }
                    }
                }
            }
            this.counter++;
        }

    });

    return adminFilters;
});