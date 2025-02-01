if (ViPscw === undefined) {
    var ViPscw = {};
}
jQuery(document).ready(function ($) {
    "use strict";

    const api = wp.customize;
    api.preview.bind("pscw_change_value", function (val) {

        const scContainer = $(".pscw_signature#pscw-container");
        const renderComponent = (data) => {
            let html = [];
            switch (data?.type) {
                case "row":
                    let cols = [];
                    for (const colId in data?.colsData) {
                        cols.push(`<div class="pscw-cols ${data?.colsData[colId].class}" id="${colId}"></div>`);
                    }
                    html.push(`<div class="pscw-row" id="${data.id}">${cols.join("")}</div>`)
                    break;
                case "table":
                    let columns = [],
                        rows = [];
                    for (const column of data?.columns) {
                        columns.push(`<th class="woo_sc_cell100"><input type="text" placeholder="..." style="width: ${(column.length + 3) + 'ch'};" value="${column}"></th>`);
                    }
                    for (const row of data?.rows) {
                        let cells = [];
                        let i = 0;
                        for (const cell of row) {
                            cells.push(`<td class="${i === 0 ? 'pscw-first-child' : ''}"><input type="text" placeholder="..." value="${cell}" style="width: ${(cell.length + 3) + 'ch'};"></td>`);
                            i++;
                        }
                        rows.push(`<tr>${cells.join("")}</tr>`);
                    }
                    html.push(`
                    <div class="woo_sc_table_scroll woo_sc_table100" id="${data?.id}" style="margin: ${data?.margin.map(item => item + "px").join(" ")}; border-radius:${data?.borderRadius.map(item => item + "px").join(" ")}">
                    <div class="woo_sc_table100-body" style="max-height: ${535 + 'px'};">
                        <div class="woo_sc_view_table">
                            <table class="woo_sc_view_table">
                            <thead class="woo_sc_table100-head ">
                                <tr class="woo_sc_row100 head">
                                ${columns.join("")}
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.join("")}
                            </tbody>
                            </table>
                        </div>
                    </div>
                    <span class="pscw-customizing-edit">&#9998;</span>
                    </div>
                    `);
                    break;
                case "text":
                    html.push(`<div class="pscw-text-editor-container" id="${data?.id}" style="margin: ${data?.margin.map(item => item + "px").join(" ")}"><div class="pscw-text-editor">${data?.value}</div><span class="pscw-customizing-edit">&#9998;</span></div>`);
                    break;
                case "image":
                    html.push(`<div class="pscw-image-container" id="${data?.id}"><img src="${data?.src}" alt="${data?.alt}" style="height: ${data?.height}${data?.heightUnit}; width: ${data?.width}${data?.widthUnit}; padding: ${data?.padding.map(item => item + "px").join(" ")}; margin:${data?.margin.map(item => item + "px").join(" ")};object-fit: ${data?.objectFit};"><span class="pscw-customizing-edit">&#9998;</span></div>`);
                    break;
            }
            return html.join("");
        };

        const element = $(`.pscw_signature #${val.data.id}`);
        switch (val.action) {
            case 'addRow':
                scContainer.append(renderComponent(val.data));
                break;
            case 'removeRow':
            case 'removeComponent':
                element.remove();
                break;
            case 'updateRow':
                let targetElement = $(`.pscw_signature #${val.data.targetId}`),
                    movedElement = element;
                switch (val.data.func) {
                    case 'after':
                        targetElement.after(movedElement);
                        break;
                    case 'before':
                        targetElement.before(movedElement);
                        break;
                    case 'append':
                        targetElement.append(movedElement);
                        break;
                }
                break;
            case 'addComponent':
                $(`#${val.data.parent}`).append(renderComponent(val.data));
                if (val.data.type === 'table') {
                    ViPscw.CustomizePreview.inputTableChange();
                    ViPscw.CustomizePreview.handelSelectTable(`.pscw_signature #${val.data.id}`);
                }
                break;
            case 'editComponent':
                ViPscw.CustomizePreview.clearCustomizingEditing();
                switch (ViPscwCusParams.position) {
                    case 'before_add_to_cart':
                    case 'after_add_to_cart':
                    case 'pop-up':
                        element.addClass("pscw-customizing-editing");
                        if (val.data.containerType === 'accordion' || val.data.containerType === 'tab') {
                            break;
                        }

                        if (element.offset().top !== 0) {
                            $('.woo_sc_scroll_content').animate({
                                scrollTop: element.offset().top,
                            }, 1000);
                        }
                        break;
                    case 'product_tabs':
                        element.addClass("pscw-customizing-editing");
                        if (element.offset().top !== 0) {
                            $("html, body").animate({
                                scrollTop: element.offset().top - 200,
                            }, 1000);
                        }
                        break;
                }
                break;
            case "exitEditComponent":
                $('.pscw-selected').removeClass('pscw-selected');
                element.removeClass("pscw-customizing-editing");
                break;
            case 'changeImageWidth':
                ViPscw.CustomizePreview.changeImageWidth(element, val.data);
                break;
            case 'changeImageHeight':
                ViPscw.CustomizePreview.changeImageHeight(element, val.data);
                break;
            case 'changeImageBorderWidth':
                ViPscw.CustomizePreview.changeImageBorderWidth(element, val.data);
                break;
            case 'changeImageBorderColor':
                ViPscw.CustomizePreview.changeImageBorderColor(element, val.data);
                break;
            case 'changeImageBorderStyle':
                ViPscw.CustomizePreview.changeImageBorderStyle(element, val.data);
                break;
            case 'uploadImage':
                let currentSrc = element.find("img").attr("src"),
                    currentAlt = element.find("img").attr("alt");
                if (currentSrc !== val.data.value.url) {
                    element.find("img").attr("src", val.data.value.url);
                }
                if (currentAlt !== val.data.value.alt) {
                    element.find("img").attr("alt", val.data.value.alt);
                }
                break;
            case 'changeImageObjectFit':
                ViPscw.CustomizePreview.changeImageObjectFit(element, val.data);
                break;
            case 'changeImagePadding':
                ViPscw.CustomizePreview.changeImagePadding(element, val.data);
                break;
            case 'changeImageMargin':
                ViPscw.CustomizePreview.changeImageMargin(element, val.data);
                break;
            case 'changeTextEditor':
                element.find(".pscw-text-editor").html(val.data.value);
                break;
            case 'addColumns':
                ViPscw.CustomizePreview.addColumns(element, val.data);
                break;
            case 'addRows':
                ViPscw.CustomizePreview.addRows(element, val.data);
                break;
            case 'removeColumns':
                for (let i = 0; i < val.data.columns; i++) {
                    let ths = element.find("table thead tr").find("th");
                    if (ths.length > 1) {
                        ths.last().remove();
                        element.find("table tbody tr").each(function () {
                            $(this).find("td").last().remove();
                        });
                    }
                }
                break;
            case 'removeRows':
                for (let i = 0; i < val.data.rows; i++) {
                    element.find("table tbody").find("tr").last().remove();
                }
                break;
            case 'changeHeaderColumn':
                ViPscw.CustomizePreview.changeHeaderBackground(element, val.data);
                ViPscw.CustomizePreview.changeTextHeader(element, val.data);
                ViPscw.CustomizePreview.changeHeaderTextBold(element, val.data);
                ViPscw.CustomizePreview.changeHeaderTextSize(element, val.data);
                ViPscw.CustomizePreview.changeEvenBackground(element, val.data);
                ViPscw.CustomizePreview.changeEvenText(element, val.data);
                ViPscw.CustomizePreview.changeOddBackground(element, val.data);
                ViPscw.CustomizePreview.changeOddText(element, val.data);
                ViPscw.CustomizePreview.changeCellTextSize(element, val.data);
                break;
            case 'changeHeaderBackground':
                ViPscw.CustomizePreview.changeHeaderBackground(element, val.data);
                break;
            case 'changeTextHeader':
                ViPscw.CustomizePreview.changeTextHeader(element, val.data);
                break;
            case 'changeHeaderTextBold':
                ViPscw.CustomizePreview.changeHeaderTextBold(element, val.data);
                break;
            case 'changeHeaderTextSize':
                ViPscw.CustomizePreview.changeHeaderTextSize(element, val.data);
                break;
            case 'changeTableStyle':
                ViPscw.CustomizePreview.changeEvenBackground(element, val.data);
                ViPscw.CustomizePreview.changeEvenText(element, val.data);
                ViPscw.CustomizePreview.changeOddBackground(element, val.data);
                ViPscw.CustomizePreview.changeOddText(element, val.data);
                break;
            case 'changeEvenBackground':
                ViPscw.CustomizePreview.changeEvenBackground(element, val.data);
                break;
            case 'changeEvenText':
                ViPscw.CustomizePreview.changeEvenText(element, val.data);
                break;
            case 'changeOddBackground':
                ViPscw.CustomizePreview.changeOddBackground(element, val.data);
                break;
            case 'changeOddText':
                ViPscw.CustomizePreview.changeOddText(element, val.data);
                break;
            case 'changeBorderColor':
                ViPscw.CustomizePreview.changeBorderColor(element, val.data);
                break;
            case 'changeCellTextSize':
                ViPscw.CustomizePreview.changeCellTextSize(element, val.data);
                break;
            case 'changeBorderWidth':
                ViPscw.CustomizePreview.changeBorderWidth(element, val.data);
                break;
            case 'changeTableBorderStyle':
                ViPscw.CustomizePreview.changeTableBorderStyle(element, val.data);
                break;
            case 'changeTableBorderRadius':
                ViPscw.CustomizePreview.changeTableBorderRadius(element, val.data);
                break;
            case 'changeTableMargin':
                ViPscw.CustomizePreview.changeTableMargin(element, val.data);
                break;
            case 'changeTableMaxHeight':
                ViPscw.CustomizePreview.changeTableMaxHeight(element, val.data);
                break;
            case 'changeTextEditorMargin':
                ViPscw.CustomizePreview.changeTextEditorMargin(element, val.data);
                break;
            case 'importCSV': {
                let lengthTr, lengthTd;
                let needrows = 0, needcolumns = 0;
                lengthTr = element.find('tr').length;
                lengthTd = element.find('tr:first').find('th').length;

                let arr = val.data.csv;
                let rows = arr.filter(item => item !== "");
                let rows_number = rows.length;
                needrows = rows_number - lengthTr;
                if (needrows < 0) {
                    let abs_need_rows = Math.abs(needrows);
                    for (let i = 0; i < abs_need_rows; i++) {
                        element.find('tr:last').remove();
                    }
                } else {
                    val.data.rows = needrows;
                    ViPscw.CustomizePreview.addRows(element, val.data);
                }

                // get max row in csv file
                let length_columns = [];
                for (let i = 0; i < rows.length; i++) {
                    let cols = rows[i].split(',');
                    length_columns[i] = cols.length;
                }

                let max_column = Math.max.apply(Math, length_columns);
                //add cols
                needcolumns = max_column - lengthTd;

                if (needcolumns < 0) {
                    let abs_need_cols = Math.abs(needcolumns);
                    for (let i = 0; i < abs_need_cols; i++) {
                        element.find('tr:first').find('th:last').remove();
                        element.find('tr:not(:first)').find('td:last').remove();
                    }
                } else {
                    val.data.columns = needcolumns;
                    ViPscw.CustomizePreview.addColumns(element, val.data);
                }

                //Update Table

                element.find('tr').each(function (i) {
                    let cols = rows[i].split(',');
                    $(this).find('input').each(function (j, ele) {
                        $(ele).val(cols[j]).trigger('input');
                    });
                });

                ViPscw.CustomizePreview.inputTableChange();

            }
                break;
        }
    });

    api.preview.bind("pscw-scan-data-table", function (val) {
        let tables = {};
        $(".pscw_signature .woo_sc_table100").each(function () {
            let tableId = $(this).attr("id");
            let tableColumns = [],
                tableRows = [];

            $(this).find("table thead tr th").each(function () {
                tableColumns.push($(this).find("input").val());
            });

            $(this).find("table tbody tr").each(function () {
                let eachRow = [];
                $(this).find("td").each(function () {
                    eachRow.push($(this).find("input").val());
                });
                tableRows.push(eachRow);
            });

            tables[tableId] = {
                columns: tableColumns,
                rows: tableRows,
            }
        });
        api.preview.send("pscw-scaned-data-table", tables);
    });
    api.preview.bind("pscw-open-popup", function (val) {
        switch (ViPscwCusParams.position) {
            case 'before_add_to_cart':
            case 'after_add_to_cart':
            case 'pop-up':
                $("#woo_sc_modal").show();
                let htmlbody = $('html, body');
                htmlbody.css({
                    overflow: 'hidden',
                    height: '100%'
                });
                break;
        }
    });

    ViPscw.CustomizePreview = {
        init() {

            if (!ViPscwCusParams.shortCode) {
                $("#tab-title-size_chart_tab").hide();
                $(".woo_sc_frontend_btn").hide();
                return;
            }

            this.elementIds = null;
            this.table = null;
            this.tr = null;
            this.cellSelectedIndex = null;
            this.trIndex = null;
            this.action = null;

            /*Some devices do not trigger window load event, we use this instead*/
            let intervalId = setInterval(() => {
                if (document.readyState === "complete") {
                    clearInterval(intervalId);
                    this.load();
                }
            }, 500);
        },

        load() {
            const _this = this;
            api.preview.send("pscw-get-elements", true);
            api.preview.bind("pscw-receive-elements", function (val) {
                if (_this.elementIds !== null) {
                    return;
                }
                _this.elementIds = val;
                const scPosition = ViPscwCusParams.position;
                switch (scPosition) {
                    case 'product_tabs':
                        const tabActive = $(".wc-tabs li[class*='active']"),
                            tab = $("#tab-size_chart_tab"),
                            tabMenu = $("#tab-title-size_chart_tab");
                        if (!tab.get(0)) {
                            break;
                        }

                        tab.append(ViPscwCusParams.shortCode);
                        $(tabActive.find("a").attr("href")).hide();
                        tabActive.removeClass("active");
                        tabMenu.addClass("active");
                        tabMenu.find('a').trigger("click");
                        tab.show();

                        $('html, body').animate({
                            scrollTop: tab.offset().top,
                        }, 1000);
                        break;
                    case 'before_add_to_cart':
                    case 'after_add_to_cart':
                    case 'pop-up':
                    case 'none': /* For the case using shortcode */
                        $("#woo_sc_modal .woo_sc_scroll_content").append(ViPscwCusParams.shortCode);
                        $("#woo_sc_modal").show();
                        $(document.body).css({overflow: 'hidden'});
                        break;
                }

                _this.inputTableChange();
                _this.loadCss(_this.elementIds);
                _this.handelSelectTable("table.woo_sc_view_table");
                _this.handleSelectElement();
            });
        },

        handleSelectElement() {
            const _this = this;
            const callback = function (e) {
                e.preventDefault();
                e.stopPropagation();

                if (!$(e.target).closest('table').length) {
                    $('.pscw-selected').removeClass('pscw-selected');
                    $('.pscw_signature #pscw-preview-table-menu').hide();
                }

                _this.clearCustomizingEditing();
                $(this).addClass("pscw-customizing-editing");

                api.preview.send("pscw-preview-open-edit-panel", {id: $(this).attr("id"), parentId: null});
            };
            $(document.body).on("click", '.woo_sc_table100, .pscw-image-container, .pscw-text-editor-container', callback);
        },

        handelSelectTable(selector) {
            const _this = this;
            let previewTableMenu = $('.pscw_signature #pscw-preview-table-menu');
            $(document.body).find(`${selector}`).on("click", 'td, th', function (e) {
                e.preventDefault();
                $('td.pscw-selected, th.pscw-selected').removeClass("pscw-selected");
                previewTableMenu.off("click");
                previewTableMenu.hide();
                $(this).addClass('pscw-selected');

            });

            $(document.body).find(".pscw_signature table.woo_sc_view_table").on("contextmenu", 'td, th', function (e) {
                e.preventDefault();
                e.stopPropagation();
                $('td.pscw-selected, th.pscw-selected').removeClass("pscw-selected");
                previewTableMenu.off("click");
                previewTableMenu.hide();
                $(this).addClass('pscw-selected');

                let cellSelected = $(".pscw-selected"),
                    cellSelectedTagName = cellSelected.prop("tagName"),
                    tr = cellSelected.parent(),
                    table = $(this).closest("table.woo_sc_view_table"),
                    tableContainer = $(this).closest(".woo_sc_table100");
                _this.table = table;
                _this.tr = tr;


                _this.clearCustomizingEditing();
                tableContainer.addClass("pscw-customizing-editing");
                api.preview.send("pscw-preview-open-edit-panel", tableContainer.attr("id"));

                if (table.find("tr").index(tr) === 0) {
                    previewTableMenu.find('li[data-action="delete-row"]').hide();
                } else {
                    previewTableMenu.find('li[data-action="delete-row"]').show();
                }

                if (tr.find("td, th").index(cellSelected) === 0) {
                    previewTableMenu.find('li[data-action="delete-col"]').hide();
                } else {
                    previewTableMenu.find('li[data-action="delete-col"]').show();
                }

                tableContainer.append(previewTableMenu);

                const tdPosition = $(this).position();
                previewTableMenu.css({
                    top: tdPosition.top + $(this).outerHeight() + "px",
                    left: tdPosition.left + "px"
                }).show();

                previewTableMenu.one('click', 'li', function () {
                    const action = $(this).data('action');
                    let trIndex = table.find("tr").index(tr),
                        cellSelectedIndex = '';
                    if (cellSelectedTagName === 'TD') {
                        cellSelectedIndex = tr.find("td").index(cellSelected);
                    } else {
                        cellSelectedIndex = tr.find("th").index(cellSelected);
                    }
                    _this.cellSelectedIndex = cellSelectedIndex;
                    _this.trIndex = trIndex;
                    _this.action = action;

                    api.preview.send("pscw-preview-get-data-table", tableContainer.attr("id"));
                });

            });

            api.preview.bind("pscw-setting-send-data-table", function (tableData) {
                const styleFistChild = [
                    `color: ${tableData.textHeader};`,
                    `font-weight: ${tableData.headerTextBold ? 'bold' : '400'};`,
                    `font-size: ${tableData.headerTextSize};`
                ].join(' ');

                const styleEvenRowBackground = tableData.columnsStyle ? '' : `background: ${tableData.evenBackground};`;
                const styleOddRowBackground = tableData.columnsStyle ? '' : `background: ${tableData.oddBackground};`;

                const styleEvenColumnBackground = tableData.columnsStyle ? `background: ${tableData.evenBackground};` : '';
                const styleOddColumnBackground = tableData.columnsStyle ? `background: ${tableData.oddBackground};` : '';

                const styleEvenRowText = `color: ${tableData.evenText};`;
                const styleEvenColumnText = tableData.columnsStyle ? styleEvenRowText : '';

                const styleOddRowText = `color: ${tableData.oddText};`;
                const styleOddColumnText = tableData.columnsStyle ? styleOddRowText : '';
                const styleBorderColor = `border-color: ${tableData.borderColor};`;
                const styleCellTextSize = `font-size: ${tableData.cellTextSize}px;`;
                const styleBorderWidth = `border-width: ${tableData.horizontalBorderWidth}px ${tableData.verticalBorderWidth}px;`;
                const styleBorderStyle = `border-style: ${tableData.horizontalBorderStyle} ${tableData.verticalBorderStyle};`;

                switch (_this?.action) {
                    case 'add-row-below':
                        let rowLength = _this?.table.find("thead tr th").length,
                            tbodyTrs = _this?.table.find("tbody tr");
                        let newTr = $(`<tr></tr>`);

                        for (let j = 0; j < rowLength; ++j) {
                            let tdClass = j === 0 ? 'pscw-first-child' : '';
                            newTr.append($(`<td class="${tdClass}"><input type="text" placeholder="..." style="width: 3ch;"></td>`));
                        }

                        let startIndex = _this?.trIndex;
                        if (_this?.trIndex === 0) {
                            newTr.insertBefore(tbodyTrs.eq(_this?.trIndex));
                        } else {
                            newTr.insertAfter(tbodyTrs.eq(_this?.trIndex - 1));
                        }

                        _this?.table.find("tbody tr").each(function (i, tr) {
                            if (i >= startIndex) {
                                let isEvenRow = i % 2 === 0;
                                $(tr).find("td").each(function (j, td) {
                                    let isEvenColumn = j % 2 !== 0;
                                    let tdBackground = (j === 0 && tableData.headerColumn) ? `background: ${tableData.headerBackground};` :
                                        (isEvenRow ? styleEvenRowBackground : styleOddRowBackground) +
                                        (isEvenColumn ? styleEvenColumnBackground : styleOddColumnBackground) +
                                        styleBorderColor +
                                        styleBorderWidth +
                                        styleBorderStyle;
                                    let tdTextStyle = (j === 0 && tableData.headerColumn) ? styleFistChild :
                                        (isEvenRow ? styleEvenRowText : styleOddRowText) +
                                        (isEvenColumn ? styleEvenColumnText : styleOddColumnText) +
                                        styleCellTextSize;

                                    let tdCssArray = tdBackground.split(';').filter(Boolean),
                                        tdInputCssArray = tdTextStyle.split(';').filter(Boolean);
                                    let tdCssObject = {},
                                        tdInputCssObject = {};

                                    tdCssArray.forEach(css => {
                                        const [property, value] = css.split(':').map(item => item.trim());
                                        tdCssObject[property] = value;
                                    });

                                    tdInputCssArray.forEach(css => {
                                        const [property, value] = css.split(':').map(item => item.trim());
                                        tdInputCssObject[property] = value;
                                    });

                                    $(td).find("input").css(tdInputCssObject);
                                    $(td).css(tdCssObject);

                                });

                            }
                        });

                        _this.inputTableChange();
                        break;
                    case 'add-col-right':
                        let isEvenThColumn = _this?.table.find("table thead tr th").length % 2 !== 0;
                        let thBackground = (tableData.headerColumn === 'column' ? (tableData.columnsStyle ? (isEvenThColumn ? `background: ${tableData.evenBackground};` : `background: ${tableData.oddBackground};`) : `background: ${tableData.oddBackground};`) : `background: ${tableData.headerBackground};`),
                            thText = (tableData.headerColumn === 'column' ? (tableData.columnsStyle ? (isEvenThColumn ? `color: ${tableData.evenText};` : `color: ${tableData.oddText};`) : `color: ${tableData.oddText};`) + styleCellTextSize + 'font-weight: 400;' : styleFistChild);
                        _this?.table.find("thead tr th").eq(_this?.cellSelectedIndex).after($(`<th class="woo_sc_cell100" style="${thBackground} ${styleBorderWidth} ${styleBorderColor} ${styleBorderStyle}"><input type="text" placeholder="..." style="width: 3ch; ${thText}"></th>`));
                        _this?.table.find("tbody tr").each(function (i, ele) {
                            let isEvenRow = i % 2 === 0,
                                isEvenColumn = ((_this?.cellSelectedIndex + 1) % 2 === 0);

                            let tdBackground = (isEvenRow ? styleEvenRowBackground : styleOddRowBackground) +
                                (isEvenColumn ? styleEvenColumnBackground : styleOddColumnBackground) +
                                styleBorderColor +
                                styleBorderWidth +
                                styleBorderStyle;
                            let tdTextStyle = (isEvenRow ? styleEvenRowText : styleOddRowText) + (isEvenColumn ? styleEvenColumnText : styleOddColumnText) + styleCellTextSize;
                            $(ele).find("td").eq(_this?.cellSelectedIndex).after($(`<td style="${tdBackground}"><input type="text" placeholder="..." style="width: 3ch; ${tdTextStyle}"></td>`));
                        });
                        _this.inputTableChange();
                        break;
                    case 'delete-col':
                        _this?.table.find("tr").each(function (i, ele) {
                            $(ele).find("td, th").eq(_this?.cellSelectedIndex).remove();
                        });
                        break;
                    case 'delete-row':
                        _this?.tr.remove();
                        break;
                }
                api.preview.send("pscw-input-table-change", true);
                $('.pscw_signature #pscw-preview-table-menu').hide();
                $('td.pscw-selected, th.pscw-selected').removeClass("pscw-selected");
            });

        },

        loadCss(elementIds) {
            for (const eleId in elementIds) {
                let element = $(`.pscw_signature #${eleId}`);
                switch (elementIds[eleId].type) {
                    case 'table':
                        this.changeHeaderBackground(element, elementIds[eleId]);
                        this.changeTextHeader(element, elementIds[eleId]);
                        this.changeHeaderTextBold(element, elementIds[eleId]);
                        this.changeHeaderTextSize(element, elementIds[eleId]);
                        this.changeEvenBackground(element, elementIds[eleId]);
                        this.changeEvenText(element, elementIds[eleId]);
                        this.changeOddBackground(element, elementIds[eleId]);
                        this.changeOddText(element, elementIds[eleId]);
                        this.changeBorderColor(element, elementIds[eleId]);
                        this.changeCellTextSize(element, elementIds[eleId]);
                        this.changeBorderWidth(element, elementIds[eleId]);
                        this.changeTableBorderStyle(element, elementIds[eleId]);
                        this.changeTableMargin(element, elementIds[eleId]);
                        this.changeTableBorderRadius(element, elementIds[eleId]);
                        this.changeTableMaxHeight(element, elementIds[eleId]);
                        break;
                    case 'image':
                        this.changeImageWidth(element, elementIds[eleId]);
                        this.changeImageHeight(element, elementIds[eleId]);
                        this.changeImageBorderWidth(element, elementIds[eleId]);
                        this.changeImageBorderColor(element, elementIds[eleId]);
                        this.changeImageBorderStyle(element, elementIds[eleId]);
                        this.changeImageObjectFit(element, elementIds[eleId]);
                        this.changeImagePadding(element, elementIds[eleId]);
                        this.changeImageMargin(element, elementIds[eleId]);
                        break;
                    case 'text':
                        this.changeTextEditorMargin(element, elementIds[eleId]);
                        break;
                }
            }
        },

        inputTableChange() {
            $(".pscw_signature#pscw-container").find("table input").on("input", function () {
                let value = $(this).val();
                $(this).css({'width': (value.length + 1) + 'ch'});
                api.preview.send("pscw-input-table-change", true);
            });
        },

        changeHeaderBackground(element, data) {
            let firstChild = element.find(".woo_sc_view_table tbody tr td.pscw-first-child:first-child"),
                ths = element.find(".woo_sc_view_table th.woo_sc_cell100");
            switch (data.headerColumn) {
                default:
                    ths.css({'background': data.headerBackground});
                    firstChild.each(function (i, ele) {
                        if (i % 2 === 0) {
                            $(ele).css({'background': data.evenBackground})
                        } else {
                            $(ele).css({'background': data.oddBackground})
                        }
                    });
                    break;
            }

        },

        changeTextHeader(element, data) {
            let inputs = element.find(".woo_sc_view_table th.woo_sc_cell100 input");
            switch (data.headerColumn) {
                default:
                    inputs.css({'color': data.textHeader});
                    break;
            }

        },

        changeHeaderTextBold(element, data) {
            let firstChildInput = element.find(".woo_sc_view_table tbody tr td.pscw-first-child:first-child input"),
                inputs = element.find(".woo_sc_view_table th.woo_sc_cell100 input");

            switch (data.headerColumn) {
                default:
                    inputs.css({'font-weight': `${data.headerTextBold ? 'bold' : '400'}`});
                    firstChildInput.css({'font-weight': '400'});
                    break;
            }

        },

        changeHeaderTextSize(element, data) {
            let inputs = element.find(".woo_sc_view_table th.woo_sc_cell100 input");

            switch (data.headerColumn) {
                default:
                    inputs.css({'font-size': `${data.headerTextSize}px`});
                    break;
            }

        },

        changeEvenBackground(element, data) {

            switch (data.headerColumn) {
                default:
                    if (data.columnsStyle) {
                        element.find('.woo_sc_view_table tbody tr td:nth-child(even)').css({'background': data.evenBackground});
                    } else {
                        element.find('.woo_sc_view_table tbody tr:nth-child(odd) td').css({'background': data.evenBackground});
                    }
                    break;
            }

        },

        changeEvenText(element, data) {

            switch (data.headerColumn) {
                default:
                    if (data.columnsStyle) {
                        element.find('.woo_sc_view_table tbody tr td:nth-child(even) input').css({'color': data.evenText});
                    } else {
                        element.find('.woo_sc_view_table tbody tr:nth-child(odd) td input').css({'color': data.evenText});
                    }
                    break;
            }

        },

        changeOddBackground(element, data) {
            let trOdd = null,
                tdOdd = null;

            switch (data.headerColumn) {
                default:
                    trOdd = element.find('.woo_sc_view_table tbody tr:nth-child(even) td');
                    tdOdd = element.find('.woo_sc_view_table tbody tr td:nth-child(odd)');
                    if (data.columnsStyle) {
                        tdOdd.css({'background': data.oddBackground});
                    } else {
                        trOdd.css({'background': `${data.oddBackground}`});
                    }
                    break;
            }

        },

        changeOddText(element, data) {
            let trOdd = null,
                tdOdd = null;

            switch (data.headerColumn) {
                default:
                    trOdd = element.find('.woo_sc_view_table tbody tr:nth-child(even) td input');
                    tdOdd = element.find('.woo_sc_view_table tbody tr td:nth-child(odd) input');
                    if (data.columnsStyle) {
                        tdOdd.css({'color': data.oddText});
                    } else {
                        trOdd.css({'color': data.oddText});
                    }
                    break;
            }

        },

        changeBorderColor(element, data) {
            element.find(".woo_sc_view_table th.woo_sc_cell100 ").css({'borderColor': data.borderColor});
            element.find(".woo_sc_view_table td ").css({'borderColor': data.borderColor});
        },

        changeCellTextSize(element, data) {
            let selector = null;

            switch (data.headerColumn) {
                default:
                    selector = element.find('.woo_sc_view_table tbody tr td input');
                    break;
            }

            selector.css({'font-size': `${data.cellTextSize}px`});
        },

        changeBorderWidth(element, data) {
            element.find(".woo_sc_view_table th.woo_sc_cell100 ").css({'borderWidth': `${data.horizontalBorderWidth}px ${data.verticalBorderWidth}px`});
            element.find(".woo_sc_view_table td ").css({'borderWidth': `${data.horizontalBorderWidth}px ${data.verticalBorderWidth}px`});
        },

        changeTableBorderStyle(element, data) {
            element.find(".woo_sc_view_table th.woo_sc_cell100 ").css({'borderStyle': `${data.horizontalBorderStyle} ${data.verticalBorderStyle}`});
            element.find(".woo_sc_view_table td ").css({'borderStyle': `${data.horizontalBorderStyle} ${data.verticalBorderStyle}`});
        },

        changeImageWidth(element, data) {
            element.find("img").css({"width": data.width + data.widthUnit});
        },

        changeImageHeight(element, data) {
            element.find("img").css({"height": data.height + data.heightUnit});
        },

        changeImageBorderWidth(element, data) {
            element.find("img").css({"borderWidth": data.borderWidth});
        },

        changeImageBorderColor(element, data) {
            element.find("img").css({"borderColor": data.borderColor});
        },

        changeImagePadding(element, data) {
            let result = data.padding.map(item => item + "px").join(" ");
            element.css({'padding': result});
        },

        changeImageMargin(element, data) {
            let result = data.margin.map(item => item + "px").join(" ");
            element.css({'margin': result});
        },

        changeImageBorderStyle(element, data) {
            if (data.borderStyle.trim().length > 0) {
                element.find("img").css({"borderStyle": data.borderStyle});
            }
        },

        changeImageObjectFit(element, data) {
            if (data.objectFit.trim().length > 0) {
                element.find("img").css({"objectFit": data.objectFit});
            }
        },

        changeTableMargin(element, data) {
            let result = data.margin.map(item => item + "px").join(" ");
            element.css({'margin': result});
        },

        changeTableBorderRadius(element, data) {
            let result = data.borderRadius.map(item => item + "px").join(" ");
            element.css({'borderRadius': result});
        },

        changeTextEditorMargin(element, data) {
            let result = data.margin.map(item => item + "px").join(" ");
            element.css({'margin': result});
        },

        changeTableMaxHeight(element, data) {
            let result = 535 + 'px';
            element.find(".woo_sc_table100-body").css({'maxHeight': result});
        },

        clearCustomizingEditing() {
            $(".pscw-customizing-editing").removeClass("pscw-customizing-editing");
        },

        addRows(element, data) {
            let rows = data.rows,
                rowLength = element.find("table thead tr th").length;

            const styleFistChild = [
                `color: ${data.textHeader};`,
                `font-weight: ${data.headerTextBold ? 'bold' : '400'};`,
                `font-size: ${data.headerTextSize}px;`
            ].join(' ');

            const styleEvenRowBackground = data.columnsStyle ? '' : `background: ${data.evenBackground};`;
            const styleOddRowBackground = data.columnsStyle ? '' : `background: ${data.oddBackground};`;

            const styleEvenColumnBackground = data.columnsStyle ? `background: ${data.evenBackground};` : '';
            const styleOddColumnBackground = data.columnsStyle ? `background: ${data.oddBackground};` : '';

            const styleEvenRowText = `color: ${data.evenText};`;
            const styleEvenColumnText = data.columnsStyle ? styleEvenRowText : '';

            const styleOddRowText = `color: ${data.oddText};`;
            const styleOddColumnText = data.columnsStyle ? styleOddRowText : '';
            const styleBorderColor = `border-color: ${data.borderColor};`;
            const styleCellTextSize = `font-size: ${data.cellTextSize}px;`;
            const styleBorderWidth = `border-width: ${data.horizontalBorderWidth}px ${data.verticalBorderWidth}px;`;
            const styleBorderStyle = `border-style: ${data.horizontalBorderStyle} ${data.verticalBorderStyle};`;

            for (let i = 0; i < rows; i++) {
                let isEvenRow = ((element.find("table tbody tr").length) % 2 === 0);
                let tr = $(`<tr></tr>`);
                for (let j = 0; j < rowLength; ++j) {
                    let isEvenColumn = j % 2 !== 0;
                    let tdClass = j === 0 ? 'pscw-first-child' : '';
                    let tdBackground = (isEvenRow ? styleEvenRowBackground : styleOddRowBackground) +
                        (isEvenColumn ? styleEvenColumnBackground : styleOddColumnBackground);

                    tdBackground += styleBorderColor +
                        styleBorderWidth +
                        styleBorderStyle;

                    let tdTextStyle = (isEvenRow ? styleEvenRowText : styleOddRowText) +
                        (isEvenColumn ? styleEvenColumnText : styleOddColumnText) +
                        styleCellTextSize;

                    tr.append($(`<td class="${tdClass}" style="${tdBackground}"><input type="text" placeholder="..." style="width: 3ch; ${tdTextStyle}"></td>`));
                }
                element.find("table tbody").append(tr);
            }

            if (element.find('.woo_sc_table100-body')[0].scrollHeight > element.find('.woo_sc_table100-body').outerHeight()) {
                element.find('.woo_sc_table100-body').scrollTop(element.find('.woo_sc_table100-body')[0].scrollHeight);
            }
            this.inputTableChange();
        },

        addColumns(element, data) {
            let columns = data.columns;

            const styleThHeader = [
                `color: ${data.textHeader};`,
                `font-weight: ${data.headerTextBold ? 'bold' : '400'};`,
                `font-size: ${data.headerTextSize};`,
            ].join(" ");

            const styleEvenRowBackground1 = data.columnsStyle ? '' : `background: ${data.evenBackground};`;
            const styleOddRowBackground1 = data.columnsStyle ? '' : `background: ${data.oddBackground};`;

            const styleEvenColumnBackground1 = data.columnsStyle ? `background: ${data.evenBackground};` : '';
            const styleOddColumnBackground1 = data.columnsStyle ? `background: ${data.oddBackground};` : '';

            const styleEvenRowText1 = `color: ${data.evenText};`;
            const styleEvenColumnText1 = data.columnsStyle ? styleEvenRowText1 : '';

            const styleOddRowText1 = `color: ${data.oddText};`;
            const styleOddColumnText1 = data.columnsStyle ? styleOddRowText1 : '';
            const styleBorderColor1 = `border-color: ${data.borderColor};`;
            const styleCellTextSize1 = `font-size: ${data.cellTextSize}px;`;
            const styleBorderWidth1 = `border-width: ${data.horizontalBorderWidth}px ${data.verticalBorderWidth}px;`;
            const styleBorderStyle1 = `border-style: ${data.horizontalBorderStyle} ${data.verticalBorderStyle};`;

            for (let i = 0; i < columns; i++) {
                let isEvenThColumn = element.find("table thead tr th").length % 2 !== 0;
                let thBackground = `background: ${data.headerBackground};`,
                    thText = styleThHeader;
                element.find("table thead tr").append($(`<th class="woo_sc_cell100" style="${thBackground} ${styleBorderWidth1} ${styleBorderColor1} ${styleBorderStyle1}"><input type="text" placeholder="..." style="width: 3ch; ${thText}"></th>`));
                element.find("table tbody tr").each(function (i, ele) {
                    let isEvenRow = i % 2 === 0,
                        isEvenColumn = (($(this).find("td").length + 1) % 2 === 0);

                    let tdBackground = (isEvenRow ? styleEvenRowBackground1 : styleOddRowBackground1) +
                        (isEvenColumn ? styleEvenColumnBackground1 : styleOddColumnBackground1) +
                        styleBorderColor1 +
                        styleBorderWidth1 +
                        styleBorderStyle1;
                    let tdTextStyle = (isEvenRow ? styleEvenRowText1 : styleOddRowText1) + (isEvenColumn ? styleEvenColumnText1 : styleOddColumnText1) + styleCellTextSize1;
                    $(this).append($(`<td style="${tdBackground}"><input type="text" placeholder="..." style="width: 3ch; ${tdTextStyle}"></td>`));
                });
            }

            if (element.find('.woo_sc_table100-body')[0].scrollWidth > element.find('.woo_sc_table100-body').outerWidth()) {
                element.find('.woo_sc_table100-body').scrollLeft(element.find('.woo_sc_table100-body')[0].scrollWidth);
            }
            this.inputTableChange();
        }
    };

    ViPscw.CustomizePreview.init();
});