if( ViPscw === undefined ) {
    var ViPscw = {};
}
jQuery(document).ready( function ( $ ) {
    "use strict";

    let checkUniqueId = [];

    window.pscwGenerateID = () => {
        let id = `ID_${Date.now()}`;
        if (checkUniqueId.includes(id)) {
            id = pscwGenerateID();
        } else {
            checkUniqueId.push(id);
        }
        return id;
    };

    /* Simulate depensOn library but only hide/show */
    $.fn.showsOn = function(target, desiredValue) {
        const $this = $(this);
        $(target).on('change', function() {
            $this.toggle($(this).val() === desiredValue);
        });

        $(target).trigger('change');

        return this;
    };

    const imgUrl = VicPscwParams.imgUrl;
    const scInterface = JSON.parse( VicPscwParams.interface );
    const pscwData = {
        title: VicPscwParams.scTitle,
        assign: VicPscwParams.assignTag,
        allow_countries: [],
        condition: VicPscwParams.assignValues,
    }
    const layoutSave = Object.assign({}, scInterface.layout);
    const elementsSave = Object.assign({}, scInterface.elementsById);
    const api = wp.customize;
    const i18n = VicPscwParams.i18n;

    class PscwComponent {
        constructor({label, id, type, value, choices, onClick, onChange, onInput,dependent}) {
            this.label = label;
            this.id = id;
            this.type = type;
            this.value = value;
            this.choices = choices;
            this.onClick = onClick;
            this.onChange = onChange;
            this.onInput = onInput;
            this.dependent = dependent;
        }

        render() {
            /* Hide upgrade button*/
            let element = $(`<li id="customize-control-${this.id || ''}" class="customize-control customize-control-text"></li>`);

            if (this.label) {
                const label = $(`<label for="" class="customize-control-title"></label>`).text(this.label);
                element.append(label);
            }

            switch (this.type) {
                case 'input':
                    element.append(this.createInput());
                    break;
                case 'inputNumber':
                    element.append(this.createInput( 'number' ));
                    break;
                case 'select':
                    element.append(this.createSelect());
                    break;
                case 'button':
                    element.append(this.createButton());
                    break;
                case 'colorPicker':
                    let colorPicker = this.createColorPicker();
                    element.append(colorPicker);
                    colorPicker.wpColorPicker({
                        change: this.onChange,
                    });
                    break;
                case 'checkbox':
                    element.append(this.createCheckbox());
                    break;
                case 'rangeSlider':
                    element.append(this.createRangeSlider())
                    break;
                case 'uploadImage':
                    element.append(this.createUploadImage());
                    break;
                case 'layout':
                    element.append(this.createLayout());
                    break;
                case 'textEditor':
                    element.append(this.createTextEditor());
                    break;
                case 'toggleSection':
                    element.append(this.createToggleSection());
                    break;
                case 'addItemTable':
                    element.append(this.createAddItemTable());
                    break;
                case 'fourDimensional':
                    element.append(this.createFourDimensional());
                    break;
                case 'radio':
                    element.append(this.createRadio());
                    break;
                case 'templateColor':
                    element.append(this.createTemplateColor());
                    break;
                case 'btnUpload':
                    element.append(this.createBtnUpload());
                    break;
                case 'upgrade':
                    element.append(this.createUpgrade());
                    break;
                case 'search':
                    element.append(this.createSearch());
                    break;
                default:
                    throw new Error(i18n.unsupported_component_type);
            }

            return element;
        }

        createUpgrade() {
            return $(`<a target="_blank" href="https://1.envato.market/zN1kJe" class="pscw-customize-customize-upgrade">
                                    Upgrade This Feature
                     </a>`);
        }

        createSearch() {
            let selectBox = $(`<select id="customize-input-${this.id || ''}" multiple class="hidden" ></select>`);
            if (this.value.assignTag === VicPscwParams.assignTag) {
                this.value.assignValues.forEach((value) => {
                    const option = $(`<option selected value="${value[0]}">${value[1]}</option>`);
                    selectBox.append(option);
                })
            }
            if (this.onChange){
                selectBox.on('change', this.onChange);
            }
            return selectBox;
        }

        createInput( type = 'text') {
            const input = $(`<input id="customize-input-${this.id || ''}" type="${type}">`).val(this.value || '');
            if (this.onInput) {
                input.on("input", this.onInput);
            }
            if (this.onChange) {
                input.on("change", this.onChange);
            }
            return input;
        }

        createSelect() {
            const select = $(`<select id="customize-input-${this.id || ''}"></select>`);
            if (typeof this.choices === 'object') {
                for (const choiceKey in this.choices) {
                    let disable = this.choices[choiceKey].includes('Premium') ? 'disabled' : '';
                    let option = $(`<option value="${choiceKey}" ${choiceKey === this.value ? 'selected' : ''} ${disable}>${this.choices[choiceKey]}</option>`);
                    select.append(option);
                }
            }

            if (this.onChange) {
                select.on("change", this.onChange);
            }

            return select;
        }

        createButton() {
            const button = $(`<button type="button" id="${this.id ? 'customize-button-' + this.id : ''}">${this.value || ''}</button>`);
            if (this.onClick) {
                button.on("click", this.onClick);
            }
            return button;
        }

        createColorPicker() {
            return $(`<input id="${this.id ? 'customize-color-picker-' + this.id : ''}" class="color-picker-hex wp-color-picker" type="text" maxlength="7" placeholder="#ffffff" data-default-color="${this.value}" value="${this.value}">`);
        }

        createCheckbox() {
            const checkboxContainer = $(`<label for="${this.id ? 'customize-input-' + this.id : ''}" class="pscw-toggle-checkbox"></label>`);
            const checkbox = $(`<input type="checkbox" name="${this.id || ''}" id="${this.id ? 'customize-input-' + this.id : ''}" value="check" ${this.value ? "checked" : ""}>`);
            if (this.onChange) {
                checkbox.on("change", this.onChange);
            }
            checkboxContainer.append(checkbox);
            checkboxContainer.append($("<span></span>"))
            return checkboxContainer;
        }

        createRangeSlider() {
            const rangeContainer = $( `<div class="pscw-customize-range"></div>` );
            const range = $( `<div class="vi-ui range pscw-customize-range1"></div>` );
            const rangeValue = $( `<input type="number" class="pscw-customize-range-value" value="${this?.value?.data?.start || 0}">` );
            const rangeCustomSelect = $( '<div class="pscw-custom-select"></div>');
            const rangeUnit = $( `<select name="" class="pscw-customize-unit"></select>` );
            if ( this?.value?.unit ) {
                let dataUnit = this?.value?.unit;
                for ( const unit in dataUnit ) {
                    let option = $( `<option value="${unit}" data-value="[${dataUnit[unit]?.min},${dataUnit[unit]?.step},${dataUnit[unit]?.max}]" ${unit === this.value?.data?.unit ? 'selected' : ''}>${dataUnit[unit]?.name}</option>` );
                    rangeUnit.append( option );
                }
            }
            rangeCustomSelect.append( rangeUnit );
            rangeContainer.append( range );
            rangeContainer.append( rangeValue );
            rangeContainer.append( rangeCustomSelect );

            return rangeContainer;

        }

        createUploadImage() {
            const uploadImage = $( `<div class="pscw-customize-upload" id="${this.id || ""}"></div>` );
            const { src, alt } = this.value;
            uploadImage.append( `<a href="javascript:void(0)" class="pscw-customize-image-wrap ${ src ? 'remove' : ''}">
                <img src="${src || ''}" alt="${alt || ''}" class="pscw-customize-image">
                <span class="pscw-customize-image-remove">&#x2715;</span>
                </a>
                <div>
                <span class="customize-control-title">URL</span>
                <input type="text" class="pscw-customize-image-url" value="${src || ''}">
                </div>
                <div>
                <span class="customize-control-title">Alt</span>
                <input type="text" class="pscw-customize-image-alt" value="${alt || ''}">
                </div>` );

            return uploadImage;
        }

        createLayout(data) {
            let children = [],
                elementsById = {};

            if (data) {
                children = data.children;
                elementsById = data.elementsById;
            }else {
                children = this.value.children;
                elementsById = this.value.elementsById;
            }
            let html = [];
            for (const child of children) {
                html.push( this.renderLayoutItem( child, elementsById ) );
            }
            return $( `<div class="pscw-customize-layout">
                                    <div class="pscw-customize-container">
                                        ${html.join('')}
                                    </div>
                                    <span class="customize-control-title">${i18n.click_items_below}</span>
                                    <div class="pscw-customize-row-list">
                                    <div class="pscw-customize-row-list-one pscw-customize-row-list-cols" data-cols="12">
                                    <div></div>
                                    </div>
                                   <a target="_blank" href="https://1.envato.market/zN1kJe" title="Premium feature" >
                                    <img src="${imgUrl}/layout_pre.jpg" alt="Premium feature">
                                    </a>
                                    </div>
                                   ` );
        }

        createTextEditor() {
            return $( `<textarea id="${this.id}" style="width: 100%;">${this.value}</textarea>` );
        }

        createToggleSection() {
            let toggleSection = $( `<div class="pscw-customize-toggle-section" id="${this.id || ''}">
                            <div class="pscw-customize-toggle-section-list-wrap">
                                <a href="javascript:void(0)" class="pscw-customize-add-toggle-section">&#65291; ${i18n.new_item}</a>
                                <div class="pscw-customize-toggle-section-list">
                                </div>
                               <div class="pscw-customize-toggle-section-tab-list">                               
                               </div>
                            </div>
                        </div>`);
            let i = 0;
            for (const child of this.value.children) {
                let toggleSectionItem = $(`<div class="pscw-customize-toggle-section-item ${i === 0 ? 'active' : ''}" id="${child.id}">
                                        <span class="pscw-customize-toggle-section-item-title">${child.title}</span>
                                        <span class="pscw-customize-toggle-section-item-remove">&#x2715;</span></div>`);

                toggleSection.find(".pscw-customize-toggle-section-list").append( toggleSectionItem );
                i++;
            }

            return toggleSection;
        }

        createAddItemTable() {
            let container = $( `<div class="pscw-customize-add-item-table"></div>` );
            let addItemButton = $( `<div class="pscw-customize-add-item-table-button pscw-customize-add-item-table-action__add">&#x2B;</div>` );
            let removeItemButton = $( `<div class="pscw-customize-add-item-table-button pscw-customize-add-item-table-action__remove">&#x2212;</div>` );
            let input = $( `<input type="number" name="${this.id || '' }" id="${this.id || '' }" value="${this.value}" min="0">` );
            if ( this.onChange ) {
                input.on( "change", this.onChange );
            }

            if ( this.onInput ) {
                input.on( "input", this.onInput );
            }

            container.append( input );
            container.append( addItemButton );
            container.append( removeItemButton );
            return container;
        }

        createFourDimensional() {
            const _this = this;
            let container = $( `<div class="pscw-customize-4-dimensional" id="${this.id || ''}"></div>` );
            let titles = [i18n.top, i18n.right, i18n.bottom, i18n.left];
            if ( this.value.length > 0 ) {
                let i = 0;
                for (const valueElement of this.value) {
                    container.append( $( `<div><input type="number" value="${valueElement}"><span>${titles[i]}</span></div>` ) );
                    i++;
                }
            }

            const getAllValues = () => {
                let values = [];
                container.find( 'input[type="number"]' ).each( function () {
                    values.push( $( this ).val() );
                } );

                return values;
            }

            container.append( $(`<span class="dashicons dashicons-admin-links" title="${i18n.link_values_together}"></span>`) );
            container.on( "input", 'input[type="number"]', function () {
                let isLinkVals = container.find( ".dashicons-admin-links" ).hasClass( "pscw-link-values-together" );
                if ( isLinkVals ) {
                    container.find( 'input[type="number"]' ).val( $( this ).val() )
                }
                if ( _this.onChange ) {
                    _this.onChange( getAllValues() );
                }
            } );

            container.on( "click", '.dashicons-admin-links', function () {
                $( this ).toggleClass( "pscw-link-values-together" );
                if ( $( this ).hasClass( "pscw-link-values-together" ) ) {
                    let firstInputVal = container.find( 'div:first-child input[type="number"]' ).val();
                    container.find( 'div:not(:first-child) input[type="number"]' ).val( firstInputVal );
                    if ( _this.onChange ) {
                        _this.onChange( getAllValues() );
                    }
                }
            } );

            return container;
        }

        createRadio() {
            let container = $( `<div class="pscw-customize-radio-container" id="${this.id || ''}"></div>` );
            if ( this.choices ) {
                for (const choicesKey in this.choices) {
                    if ( ( choicesKey === 'column' || choicesKey === 'both' ) ) {
                        container.append( $( `<div class="pscw-customize-radio-item">
                                                <a target="_blank" href="https://1.envato.market/zN1kJe">
                                                    <label for="${this.id + '-' + choicesKey}">${this.choices[choicesKey]}</label>
                                                </a>
                                               </div>` ) );
                    }else {
                        container.append( $( `<div class="pscw-customize-radio-item">
                        <input type="radio" name="${this.id || ''}" id="${this.id + '-' + choicesKey}" value="${choicesKey}" ${ ( choicesKey === this.value ) ? 'checked' : ''}>
                        <label for="${this.id + '-' + choicesKey}">${this.choices[choicesKey]}</label>
                        </div>` ) );
                    }

                }
            }
            if ( this.onInput ) {
                container.find( `input[name="${this.id}"]` ).on( "input", this.onInput );
            }

            return container;
        }

        createBtnUpload() {
            let container = $( `<div class="pscw-customize-btn-upload-container"></div>` );
            let button = $( `<div class="pscw-customize-btn-import">${this.value}</div>` );
            let input = $( `<input type="file" hidden>` );
            let uploadPre = $(` <a target="_blank" href="https://1.envato.market/zN1kJe" title="Premium feature" >
                                    <img src="${imgUrl}/upload_pre.jpg" alt="Premium feature">
                                    </a>`)
            container.append( input );
            container.append( button );
            container.append( uploadPre );
            button.on( "click", function () {
                input.trigger( "click" );
            } );
            if ( this.onChange ) {
                input.on( "change", this.onChange )
            }
            return container;
        }

        createTemplateColor() {

            let html = [];
            let container = $( `<div class="pscw-customize-template-color-container" id="${this.id || ''}"></div>` );
            for (const choicesKey in this.choices) {
                let color = this.choices[choicesKey];
                html.push(`<div class="pscw-customize-template-color-item" data-color="${choicesKey}" style="background: ${color.header}"></div>`);
            }
            container.append( html.join('') );
            if ( this.onClick ) {
                container.on( "click", this.onClick );
            }
            return container;
        }

        renderLayoutItem( childId, elements ) {
            if ( ! elements[childId] ) {
                return "";
            }

            let element = elements[childId];
            let html = [];

            switch ( element.type ) {
                case 'row':
                    let cols = [];
                    for (const child of element.children) {
                        cols.push( this.renderLayoutItem( child, elements ) );
                    }
                    html.push( `
                    <div class="pscw-customize-row" id="${element.id}">
                        <span class="pscw-customize-row-remove">&#x2715;</span>
                        ${cols.join('')}
                    </div>` );
                    break;
                case 'column':
                    let children = [];
                    for (const child of element.children) {
                        children.push( this.renderLayoutItem( child, elements ) );
                    }
                    html.push(`
                    <div class="pscw-customize-col ${element.settings.class}" id="${element.id}">
                        <div class="pscw-customize-list-component pscw-connectedSortable">${children.join('')}</div>
                        <div class="pscw-customize-open-tab-component">&#65291;</div>
                    </div>`);
                    break;
                case 'table':
                case 'text':
                case 'image':
                    html.push(`
                    <div class="pscw-customize-component" id="${element.id}">
                         ${element.type.charAt(0).toUpperCase() + element.type.slice(1)}
                         <div class="pscw-customize-component-action pscw-customize-component-action__edit">&#9998;</div>
                         <div class="pscw-customize-component-action pscw-customize-component-action__remove">&#x2715;</div>
                    </div>
                    `);
                    break;
            }

            return html.join('');
        }

    }

    api.previewer.bind( "pscw-input-table-change", function ( val ) {
        if ( val ) {
            api('woo_sc_setting[cus_design]').set(!api('woo_sc_setting[cus_design]').get());
        }
    } );

    api.previewer.bind( "pscw-preview-get-data-table", function ( id ) {
        if ( id ) {
            api.previewer.send( "pscw-setting-send-data-table", elementsSave[id] );
        }
    } );


    ViPscw.CustomizeSettings = {
        init() {
            this.selectedCol = null;
            this.selectedEditEle = null;
            this.back = null;
            this.selectedContainerType = 'container';
            this.borderOptions = {
                solid: "Solid",
                dotted: "Dotted",
                dashed: "Dashed",
                double: "Double",
                groove: "Groove",
                ridge: "Ridge",
                inset: "Inset",
                outset: "Outset",
            };
            this.templateColors = {
                grey: {
                    header: '#F6F6F6',
                    even: '#FFFFFF',
                    odd: '#F6F6F6',
                    textHeader: '#000000',
                    border: '#F6F6F6',
                    headerTextBold: true,
                },
                red: {
                    header: '#BD171B',
                    even: '#FAF6F6',
                    odd: '#FFF0F0',
                    textHeader: '#ffffff',
                    border: '#BD171B',
                    headerTextBold: true,
                },
                orange: {
                    header: '#FF801F',
                    even: '#FFF9F4',
                    odd: '#FFF2E8',
                    textHeader: '#ffffff',
                    border: '#FF801F',
                    headerTextBold: true,
                },
                blue: {
                    header: '#70B5D5',
                    even: '#F0FAFF',
                    odd: '#DCF4FF',
                    border: '#70B5D5',
                },
                black: {
                    header: '#323232',
                    even: '#F8F8F8',
                    odd: '#EAEAEA',
                    textHeader: '#ffffff',
                    border: '#323232',
                    headerTextBold: true,
                },
                brightCyan: {
                    header: '#00D1D1',
                    even: '#F2FFFF',
                    odd: '#E5FFFF',
                    border: '#00D1D1',
                    headerTextBold: true,
                },
                amberYellow: {
                    header: '#FFCA0A',
                    even: '#FFFCF2',
                    odd: '#FFF9E3',
                    border: '#FFCA0A',
                    headerTextBold: true,
                },
                green: {
                    header: '#41B729',
                    even: '#F2FFEF',
                    odd: '#E1FBDB',
                    border: '#41B729',
                    headerTextBold: true,
                },
                tealBlue: {
                    header: '#18889D',
                    even: '#E7FBFF',
                    odd: '#D2F3FA',
                    border: '#18889D',
                    headerTextBold: true,
                },
                seaweedGreen: {
                    header: '#456E68',
                    even: '#EAFBF8',
                    odd: '#D2F1ED',
                    textHeader: '#ffffff',
                    border: '#456E68',
                    headerTextBold: true,
                },
                pastelPink: {
                    header: '#F3A5AD',
                    even: '#FFFCFC',
                    odd: '#FFF0F0',
                    textHeader: '#ffffff',
                    border: '#F3A5AD',
                    headerTextBold: true,
                },
                skyBlue: {
                    header: '#1B90FF',
                    even: '#F3F9FF',
                    odd: '#EAF5FF',
                    textHeader: '#ffffff',
                    border: '#1B90FF',
                    headerTextBold: true,
                },
                lavenderPurple: {
                    header: '#8D72FF',
                    even: '#F9F8FF',
                    odd: '#F3F0FF',
                    textHeader: '#ffffff',
                    border: '#8D72FF',
                    headerTextBold: true,
                },
                electricPink: {
                    header: '#FF32F9',
                    even: '#FFF6FF',
                    odd: '#FFEEFF',
                    textHeader: '#ffffff',
                    border: '#FF32F9',
                    headerTextBold: true,
                }

            };
            this.pscwId = null;

            this.load();
            this.handleDesign();
            this.handleImage();
            this.handleTextEditor();
            this.handleTable();
            this.handleOpenEditPanel();
            $( ".customize-section-back" ).on( "click", {self: this}, this.sectionBack );

            $( "#save" ).on( "click", this.save.bind(this) );

        },

        handleOpenEditPanel() {
            const _this = this;
            api.previewer.bind( "pscw-preview-open-edit-panel", function ( obj ) {
                if ( obj.id ) {
                    if ( _this.selectedEditEle ) {
                        if ( _this.selectedEditEle.id === obj.id ) {
                            return;
                        }
                        if ( _this.selectedEditEle.type === elementsSave[obj.id].type ) {
                            api.section(`pscw_customizer_${_this.selectedEditEle.type}`).expanded(false);
                        }
                    }

                    if ( obj.parentId ) {
                        _this.back = elementsSave[obj.parentId];
                    }else {
                        _this.back = null;
                    }

                    _this.selectedEditEle = elementsSave[obj.id];
                    _this.handleDataForOpenEditComponent( false );
                }
            });
        },

        save(e) {
            const pscwId = this.pscwId;
            if ( ! pscwId ) {
                return;
            }
            /* Get shortcode data */
            api.previewer.send( "pscw-scan-data-table", true );

            api.previewer.bind( "pscw-scaned-data-table", function ( val ) {

                for (const valKey in val) {
                    elementsSave[valKey].columns = val[valKey].columns;
                    elementsSave[valKey].rows = val[valKey].rows;
                }

                let data = {
                    action: 'pscw_save_size_chart_data',
                    nonce: VicPscwParams.nonce,
                    value: JSON.stringify({layout: layoutSave, elementsById: elementsSave}),
                    pscwId: pscwId,
                    pscwData: JSON.stringify(pscwData),
                }

                $.ajax({
                    url: VicPscwParams.ajaxUrl,
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    beforeSend: function () {},
                    success: function (res) {
                        if ( res?.success ) {
                            console.log( res?.data );
                        }
                    },
                    error: function (res) {
                        console.log( res?.data );
                    },
                    complete: function () {}
                });
            } );


        },

        sectionBack(e) {
            const _this = e.data.self;

            let submenu = [
                'text',
                'image',
                'table',
                'design',
            ];

            let id = $(this).parent().parent().parent().prop('id').replace("sub-accordion-section-pscw_customizer_", "");

            if ( submenu.indexOf( id ) > -1 ) {
                _this.transmitData( "exitEditComponent", {id:_this.selectedEditEle?.id}, false );
                api.section("pscw_customizer_design").expanded(true);
                _this.selectedEditEle = null;
                _this.back = null;

            }
            if ( ! VicPscwParams.customizeMode && id === 'design' ) {
                api.section( 'pscw_customizer_design' ).expanded(false);
                api.panel( 'pscw_size_chart_customizer' ).expanded(false);
            }
        },

        load() {

            const _this = this;
            const urlObj = new URL( window.location.href );
            const searchParams = new URLSearchParams(urlObj.search);
            const panel = searchParams.get('autofocus[panel]'),
                section = searchParams.get('autofocus[section]');

            _this.pscwId = searchParams.get('pscw_id') || VicPscwParams.currentSizeChart;
            if ( panel ) {
                api.panel( panel ).expanded(true);
            }
            if ( section ) {
                api.section( section ).expanded(true);
            }

            $( "#customize-outer-theme-controls" ).append(`
            <ul class="customize-pane-child accordion-section-content accordion-section control-section control-section-outer" id="pscw-customize-component-panel">
                <li class="customize-control">
                    <div class="customize-section-title pscw-customize-component-panel-title">${i18n.select_component}
                    <span class="pscw-customize-component-panel-close">&#x2715;</span></div>
                    <div class="pscw-customize-component-panel-list">
                        <div class="pscw-customize-component-panel-item" data-component="image">
                            <span class="dashicons dashicons-cover-image"></span>
                            ${i18n.image}
                        </div>
                        <div class="pscw-customize-component-panel-item" data-component="table">
                            <span class="dashicons dashicons-editor-table"></span>
                            ${i18n.table}
                        </div>
                        <div class="pscw-customize-component-panel-item" data-component="text">
                            <span class="dashicons dashicons-text"></span>
                            ${i18n.text}
                        </div>
                        <a target="_blank" href="https://1.envato.market/zN1kJe" class="pscw-customize-component-panel-item" data-component="tab">
                            <span class="dashicons dashicons-table-row-after"></span>
                            ${i18n.tab} (Premium)
                        </a>
                        <a target="_blank" href="https://1.envato.market/zN1kJe" class="pscw-customize-component-panel-item" data-component="accordion">
                            <span class="dashicons dashicons-menu-alt3"></span>
                            ${i18n.accordion} (Premium)
                        </a>
                        <a target="_blank" href="https://1.envato.market/zN1kJe" class="pscw-customize-component-panel-item" data-component="divider">
                            <span class="dashicons dashicons-image-flip-vertical"></span>
                            ${i18n.divider} (Premium)
                        </a>
                    </div>
                </li>
            </ul>
            `);
            // Point start to add shortcode to place
            api.previewer.bind( "pscw-get-elements", function (val) {
                if ( _this.pscwId ) {
                    api.previewer.send("pscw-receive-elements", elementsSave );
                }
            })
            $( '#accordion-panel-pscw_size_chart_customizer' ).on( "click", function ( e ) {
                api.section( 'pscw_customizer_design' ).expanded(true);
                api.previewer.send("pscw-open-popup", true );
            } );
        },

        handleDesign() {

            let selectSizeChart = new PscwComponent({
                label: i18n.select_size_chart,
                type: 'select',
                id: 'pscw-select-size-chart',
                value: VicPscwParams.currentSizeChart,
                choices: VicPscwParams.sizeCharts,
                onChange: function (e) {
                    let value = $( this ).val();
                    // $(window).off('beforeunload');
                    const urlObj = new URL( window.location.href );
                    urlObj.searchParams.set('pscw_id', value);

                    window.location.href = urlObj.toString();
                }
            });

            let sizeChartTitle = new PscwComponent({
                label: i18n.size_chart_title,
                type: 'input',
                id: 'pscw-size-chart-title',
                value: VicPscwParams.scTitle,
                onInput: function(e) {
                    pscwData.title = $(this).val();
                    ViPscw.CustomizeSettings.transmitData('','',true);
                }
            })

            let selectCountry = new PscwComponent( {
                label: i18n.select_country,
                type: 'upgrade',
            } );

            let assign = new PscwComponent({
                label: i18n.assign,
                type: 'select',
                id: 'pscw-size-chart-assign',
                value: VicPscwParams.assignTag,
                choices: JSON.parse(VicPscwParams.assignOptions),
                onChange: function(e) {
                    pscwData.assign = $(this).val();
                    ViPscw.CustomizeSettings.transmitData('','',true);
                }
            })

            let searchProduct = new PscwComponent({
                label: i18n.search_product,
                type: 'search',
                id: 'pscw-size-chart-search-product',
                value: {
                    assignTag: 'products',
                    assignValues: VicPscwParams.assignValues
                },
                onChange: function (e) {
                    pscwData.condition = $(this).val();
                    ViPscw.CustomizeSettings.transmitData('','',true);
                }
            })

            let searchProductCat = new PscwComponent({
                label: i18n.search_product_cat,
                type: 'search',
                id: 'pscw-size-chart-search-product-cat',
                value: {
                    assignTag: 'product_cat',
                    assignValues: VicPscwParams.assignValues
                },
                onChange: function(e) {
                    pscwData.condition = $(this).val();
                    ViPscw.CustomizeSettings.transmitData('','',true);
                }
            })


            let layoutComponent = new PscwComponent({
                label: i18n.layout,
                value: {
                    children: scInterface?.layout?.children,
                    elementsById: scInterface?.elementsById,
                },
                type: 'layout',
            });

            const container = $( "#sub-accordion-section-pscw_customizer_design" );
            container.append( selectSizeChart.render() );
            container.append( sizeChartTitle.render() );
            container.append( selectCountry.render() );
            container.append( assign.render() );
            container.append( searchProduct.render() );
            container.append( searchProductCat.render() );
            container.append( layoutComponent.render() );

            $( document.body ).on( "click", ".pscw-customize-row-list-cols", {self:this,}, this.addRow );
            $( document.body ).on( "click", ".pscw-customize-row-remove", {self:this}, this.removeRow );
            $( document.body ).on( "click", ".pscw-customize-open-tab-component", {self:this}, this.openComponentPanel );
            $( document.body ).on( "click", ".pscw-customize-component-action__remove", {self:this}, this.removeComponent );
            $( document.body ).on( "click", ".pscw-customize-component-action__edit", {self:this}, this.openEditComponent );
            $( document.body ).on( "click", ".pscw-customize-component-panel-close",{self:this}, this.closeComponentPanel );
            $( document.body ).on( "click", "#publish-settings",{self:this}, this.refreshComponentPanel );
            $( document.body ).on( "click", ".pscw-customize-component-panel-item",{self:this}, this.addComponent );

            this.handleSortAble();

            /*Check component quota, disabled if over quota */
            this.changeComponentStatus();

            /* Hide elements dependent*/
            $('#customize-input-pscw-size-chart-search-product').select2( {
                width: '100%',
                minimumInputLength: 3,
                placeholder: 'Product name...',
                allowClear: true,
                ajax: {
                    type: 'post',
                    url: VicPscwParams.ajaxUrl,
                    data: function (params) {
                        let query = {
                            key_search: params.term,
                            action: 'pscw_search_product',
                            nonce: VicPscwParams.nonce,
                        };
                        return query;
                    },
                    processResults: function (data) {
                        return data ? data : {results: []};
                    }
                }
            })

            $('#customize-control-pscw-size-chart-search-product').showsOn('#customize-input-pscw-size-chart-assign', 'products');

            const termSearch = ({placeholder, taxonomy})=> {
                return {
                    width: '100%',
                    minimumInputLength: 1,
                    placeholder: placeholder,
                    allowClear: true,
                    ajax: {
                        type: 'post',
                        url: VicPscwParams.ajaxUrl,
                        data: function (params) {
                            let query = {
                                taxonomy: taxonomy,
                                key_search: params.term,
                                action: 'pscw_search_term',
                                nonce: VicPscwParams.nonce,
                            };
                            return query;
                        },
                        processResults: function (data) {
                            return data ? data : {results: []};
                        }
                    }
                }
            }
            $('#customize-input-pscw-size-chart-search-product-cat').select2(termSearch({placeholder : 'Product categories...', taxonomy: 'product_cat'}));
            $('#customize-control-pscw-size-chart-search-product-cat').showsOn('#customize-input-pscw-size-chart-assign', 'product_cat');
        },


        handleImage() {
            const _this = this;
            const container = $( "#sub-accordion-section-pscw_customizer_image" );
            api.section( "pscw_customizer_image", function (section) {
                section.expanded.bind(function (isExpanded) {
                    if (isExpanded && _this.selectedEditEle !== null) {
                        let uploadImageComponentData = {
                            label: i18n.upload_image,
                            id: "pscw-upload-image",
                            type: 'uploadImage',
                            value: {
                                src: _this.selectedEditEle?.src,
                                alt: _this.selectedEditEle?.alt,
                            }
                        };

                        let uploadImageComponent = new PscwComponent( uploadImageComponentData );

                        let widthImageData = {
                            label: i18n.width,
                            id: "pscw-image-width",
                            type: "rangeSlider",
                            value: {
                                data : {
                                    unit: _this.selectedEditEle?.widthUnit || '%',
                                    value:_this.selectedEditEle?.width || 100,
                                },
                                unit: {
                                    px: {
                                        name: 'px',
                                        min: 0,
                                        max: 1000,
                                        step: 1
                                    },
                                    '%': {
                                        name: '%',
                                        min: 0,
                                        max: 100,
                                        step: 1,
                                    },
                                    em: {
                                        name: 'em',
                                        min: 0,
                                        max: 10,
                                        step: 0.1,
                                    },
                                    rem: {
                                        name: 'rem',
                                        min: 0,
                                        max: 10,
                                        step: 0.1,
                                    },
                                    vw: {
                                        name: 'vw',
                                        min: 0,
                                        max: 100,
                                        step: 1,
                                    },
                                }
                            },
                        };
                        let widthImage = new PscwComponent( widthImageData );

                        let heightImage = new PscwComponent( {
                            label: i18n.height,
                            type: 'upgrade',
                        } );

                        let borderStyleImage = new PscwComponent( {
                            label: i18n.border_style,
                            type: 'upgrade',
                        } );

                        let borderWidthImage = new PscwComponent( {
                            label: i18n.border_style,
                            type: 'upgrade',
                        } );

                        let borderColorImage = new PscwComponent( {
                            label: i18n.border_color,
                            type: 'upgrade',
                        } );

                        let paddingImage = new PscwComponent({
                            label: i18n.padding,
                            value: _this.selectedEditEle.margin || [0,0,0,0],
                            type: 'fourDimensional',
                            id: 'pscw-padding-image',
                            onChange: function (data) {
                                _this.transmitData( "changeImagePadding", {id: _this.selectedEditEle?.id, padding: data } );
                                elementsSave[_this.selectedEditEle.id].padding = data;
                            }
                        });

                        let marginImage = new PscwComponent({
                            label: i18n.margin,
                            value: _this.selectedEditEle.margin || [0,0,0,0],
                            type: 'fourDimensional',
                            id: 'pscw-margin-image',
                            onChange: function (data) {
                                _this.transmitData( "changeImageMargin", {id: _this.selectedEditEle?.id, margin: data } );
                                elementsSave[_this.selectedEditEle.id].margin = data;
                            }
                        });

                        let objectFitImage = new PscwComponent( {
                            label: i18n.object_fit,
                            type: 'upgrade',
                        } );

                        container.append( uploadImageComponent.render() );
                        _this.handleUploadImage( uploadImageComponentData, 'uploadImage' );
                        container.append( widthImage.render() );
                        _this.handleRangeSlider( widthImageData, 'changeImageWidth', 'width', 'widthUnit' );
                        container.append( heightImage.render() );
                        container.append( paddingImage.render() );
                        container.append( marginImage.render() );
                        container.append( objectFitImage.render() );
                        container.append( borderStyleImage.render() );
                        container.append( borderWidthImage.render() );
                        container.append( borderColorImage.render() );
                    }else {
                        container.children().not(".customize-section-description-container").remove();
                    }
                });
            } );
        },

        handleTextEditor() {
            const _this = this;
            const container = $( "#sub-accordion-section-pscw_customizer_text" );
            api.section( "pscw_customizer_text", function (section) {
                section.expanded.bind(function (isExpanded) {
                    if (isExpanded && _this.selectedEditEle !== null) {
                        let textEditorComponentData = {
                            label: i18n.text,
                            id: "pscw-text-editor",
                            type: 'textEditor',
                            value: _this.selectedEditEle?.value,
                        };

                        let textEditorComponent = new PscwComponent( textEditorComponentData );
                        let textEditorMargin = new PscwComponent({
                            label: i18n.margin,
                            value: _this.selectedEditEle.margin || [0,0,0,0],
                            type: 'fourDimensional',
                            id: 'pscw-margin-accordion',
                            onChange: function (data) {
                                _this.transmitData( "changeTextEditorMargin", {id: _this.selectedEditEle?.id, margin: data } );
                                elementsSave[_this.selectedEditEle.id].margin = data;
                            }
                        });
                        container.append( textEditorComponent.render() );
                        container.append( textEditorMargin.render() );

                        if ( typeof wp.editor.getDefaultSettings === "undefined" ) {
                            wp.editor.getDefaultSettings = function () {
                                return tinymce.settings;
                            }
                        }

                        wp.editor.remove( textEditorComponentData.id );
                        wp.editor.initialize( textEditorComponentData.id, {
                            tinymce: {
                                theme: "modern",
                                skin: "lightgray",
                                language: "en",
                                formats: {
                                    alignleft: [
                                        {selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", styles: {textAlign: "left"}},
                                        {selector: "img,table,dl.wp-caption", classes: "alignleft"}
                                    ],
                                    aligncenter: [
                                        {selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", styles: {textAlign: "center"}},
                                        {selector: "img,table,dl.wp-caption", classes: "aligncenter"}
                                    ],
                                    alignright: [
                                        {selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li", styles: {textAlign: "right"}},
                                        {selector: "img,table,dl.wp-caption", classes: "alignright"}
                                    ],
                                    strikethrough: {inline: "del"}
                                },
                                relative_urls: false,
                                remove_script_host: false,
                                convert_urls: false,
                                browser_spellcheck: true,
                                fix_list_elements: true,
                                entities: "38,amp,60,lt,62,gt",
                                entity_encoding: "raw",
                                keep_styles: false,
                                cache_suffix: "wp-mce-49110-20201110",
                                resize: "vertical",
                                menubar: false,
                                branding: false,
                                preview_styles: "font-family font-size font-weight font-style text-decoration text-transform",
                                end_container_on_empty_block: true,
                                wpeditimage_html5_captions: true,
                                wp_lang_attr: "en-US",
                                wp_keep_scroll_position: false,
                                wp_shortcut_labels: {
                                    "Heading 1": "access1",
                                    "Heading 2": "access2",
                                    "Heading 3": "access3",
                                    "Heading 4": "access4",
                                    "Heading 5": "access5",
                                    "Heading 6": "access6",
                                    "Paragraph": "access7",
                                    "Blockquote": "accessQ",
                                    "Underline": "metaU",
                                    "Strikethrough": "accessD",
                                    "Bold": "metaB",
                                    "Italic": "metaI",
                                    "Code": "accessX",
                                    "Align center": "accessC",
                                    "Align right": "accessR",
                                    "Align left": "accessL",
                                    "Justify": "accessJ",
                                    "Cut": "metaX",
                                    "Copy": "metaC",
                                    "Paste": "metaV",
                                    "Select all": "metaA",
                                    "Undo": "metaZ",
                                    "Redo": "metaY",
                                    "Bullet list": "accessU",
                                    "Numbered list": "accessO",
                                    "Insert\/edit image": "accessM",
                                    "Insert\/edit link": "metaK",
                                    "Remove link": "accessS",
                                    "Toolbar Toggle": "accessZ",
                                    "Insert Read More tag": "accessT",
                                    "Insert Page Break tag": "accessP",
                                    "Distraction-free writing mode": "accessW",
                                    "Add Media": "accessM",
                                    "Keyboard Shortcuts": "accessH"
                                },
                                plugins: "charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview",
                                selector: "#pscw-text-editor",
                                wpautop: true,
                                indent: false,
                                toolbar1: "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,spellchecker,fullscreen,wp_adv",
                                toolbar2: "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
                                tabfocus_elements: ":prev,:next",
                                body_class: "excerpt post-type-product post-status-publish page-template-default locale-en-us",
                                setup: function (editor) {
                                    editor.on( "input change", function (e) {
                                        _this.transmitData( "changeTextEditor", {id:_this.selectedEditEle?.id, value: editor.getContent()} );
                                        elementsSave[_this.selectedEditEle?.id].value = editor.getContent();
                                    } );
                                }
                            },
                            mediaButtons: true,
                            quicktags:true,
                        } );
                    }else {
                        // tinymce.remove();
                        container.children().not(".customize-section-description-container").remove();
                    }
                });
            });
        },

        handleTable() {
            const _this = this;
            const container = $( "#sub-accordion-section-pscw_customizer_table" );
            container.on( "click", "#customize-control-pscw-add-columns-table .pscw-customize-add-item-table-action__add", {self:this}, this.addColumns );
            container.on( "click", "#customize-control-pscw-add-rows-table .pscw-customize-add-item-table-action__add", {self:this}, this.addRows );
            container.on( "click", "#customize-control-pscw-add-columns-table .pscw-customize-add-item-table-action__remove", {self:this}, this.removeColumns );
            container.on( "click", "#customize-control-pscw-add-rows-table .pscw-customize-add-item-table-action__remove", {self:this}, this.removeRows );

            api.section( "pscw_customizer_table", function (section) {
                section.expanded.bind(function (isExpanded) {
                    if (isExpanded && _this.selectedEditEle !== null) {

                        let addColumns = new PscwComponent({
                            label: i18n.add_columns,
                            type: 'addItemTable',
                            id: 'pscw-add-columns-table',
                            value: 1,
                            onInput: function (e) {
                                let value = $( this ).val();
                                if ( value === '' || parseFloat( value ) < 0 ) {
                                    $( this ).val( '' );
                                }
                            }
                        });

                        let addRows = new PscwComponent({
                            label: i18n.add_rows,
                            type: 'addItemTable',
                            id: 'pscw-add-rows-table',
                            value: 1,
                            onInput: function (e) {
                                let value = $( this ).val();
                                if ( value === '' || parseFloat( value ) < 0 ) {
                                    $( this ).val( '' );
                                }
                            }
                        });

                        let headerBackground = new PscwComponent({
                            label: i18n.header_background,
                            type: 'colorPicker',
                            value: _this.selectedEditEle.headerBackground,
                            id: 'pscw-header-background-table',
                            onChange: function (event, ui) {
                                let color = ui.color.toString();
                                _this.transmitData( "changeHeaderBackground", {
                                    id: _this.selectedEditEle?.id,
                                    headerColumn: _this.selectedEditEle.headerColumn,
                                    headerBackground: color,
                                    evenBackground: _this.selectedEditEle.evenBackground,
                                } );
                                elementsSave[_this.selectedEditEle.id].headerBackground = color;
                            },
                        });

                        let textHeader = new PscwComponent({
                            label: i18n.text_header,
                            type: 'colorPicker',
                            value: _this.selectedEditEle.textHeader,
                            id: 'pscw-text-header-table',
                            onChange: function (event, ui) {
                                let color = ui.color.toString();
                                _this.transmitData( "changeTextHeader", {id: _this.selectedEditEle?.id, headerColumn: _this.selectedEditEle.headerColumn, textHeader: color } );
                                elementsSave[_this.selectedEditEle.id].textHeader = color;
                            },
                        });

                        let headerTextBold = new PscwComponent({
                            label: i18n.header_text_bold,
                            type: 'checkbox',
                            id: 'pscw-header-text-bold-table',
                            value: _this.selectedEditEle.headerTextBold,
                            onChange: function (e) {
                                let value = $(this).prop('checked');
                                _this.transmitData( "changeHeaderTextBold", {id: _this.selectedEditEle.id, headerColumn: _this.selectedEditEle.headerColumn, headerTextBold : value} );
                                elementsSave[_this.selectedEditEle.id].headerTextBold = value;
                            },
                        });

                        let headerTextSize = new PscwComponent( {
                            label: i18n.header_text_size,
                            type: 'upgrade',
                        } );

                        let columnsStyle = new PscwComponent({
                            label: i18n.columns_style,
                            type: 'checkbox',
                            id: 'pscw-columns-style-table',
                            value: _this.selectedEditEle.columnsStyle,
                            onChange: function (e) {
                                let value = $(this).prop('checked');
                                _this.transmitData( "changeTableStyle", {
                                    id: _this.selectedEditEle.id,
                                    columnsStyle : value,
                                    evenBackground: _this.selectedEditEle.evenBackground,
                                    evenText: _this.selectedEditEle.evenText,
                                    headerColumn: _this.selectedEditEle.headerColumn,
                                    oddBackground: _this.selectedEditEle.oddBackground,
                                    oddText: _this.selectedEditEle.oddText,
                                } );
                                elementsSave[_this.selectedEditEle.id].columnsStyle = value;
                            },
                        });

                        let evenBackground = new PscwComponent({
                            label: i18n.even_background,
                            type: 'colorPicker',
                            value: _this.selectedEditEle.evenBackground,
                            id: 'pscw-even-background-table',
                            onChange: function (event, ui) {
                                let color = ui.color.toString();
                                _this.transmitData( "changeEvenBackground", {
                                    id: _this.selectedEditEle?.id,
                                    columnsStyle: _this.selectedEditEle.columnsStyle,
                                    evenBackground: color,
                                    headerColumn: _this.selectedEditEle.headerColumn,
                                } );
                                elementsSave[_this.selectedEditEle.id].evenBackground = color;
                            },
                        });

                        let evenText = new PscwComponent({
                            label: i18n.even_text,
                            type: 'colorPicker',
                            value: _this.selectedEditEle.evenText,
                            id: 'pscw-even-text-table',
                            onChange: function (event, ui) {
                                let color = ui.color.toString();
                                _this.transmitData( "changeEvenText", {
                                    id: _this.selectedEditEle?.id,
                                    columnsStyle: _this.selectedEditEle.columnsStyle,
                                    evenText: color,
                                    headerColumn: _this.selectedEditEle.headerColumn,
                                } );
                                elementsSave[_this.selectedEditEle.id].evenText = color;
                            },
                        });

                        let oddBackground = new PscwComponent({
                            label: i18n.odd_background,
                            type: 'colorPicker',
                            value: _this.selectedEditEle.oddBackground,
                            id: 'pscw-odd-background-table',
                            onChange: function (event, ui) {
                                let color = ui.color.toString();
                                _this.transmitData( "changeOddBackground", {
                                    id: _this.selectedEditEle?.id,
                                    columnsStyle: _this.selectedEditEle.columnsStyle,
                                    oddBackground: color,
                                    headerColumn: _this.selectedEditEle.headerColumn,
                                } );
                                elementsSave[_this.selectedEditEle.id].oddBackground = color;
                            },
                        });

                        let oddText = new PscwComponent({
                            label: i18n.odd_text,
                            type: 'colorPicker',
                            value: _this.selectedEditEle?.oddText,
                            id: 'pscw-odd-text-table',
                            onChange: function (event, ui) {
                                let color = ui.color.toString();
                                _this.transmitData( "changeOddText", {
                                    id: _this.selectedEditEle?.id,
                                    columnsStyle: _this.selectedEditEle.columnsStyle,
                                    oddText: color,
                                    headerColumn: _this.selectedEditEle.headerColumn,
                                } );
                                elementsSave[_this.selectedEditEle.id].oddText = color;
                            },
                        });

                        let borderColor = new PscwComponent({
                            label: i18n.border_color,
                            type: 'colorPicker',
                            value: _this.selectedEditEle.borderColor,
                            id: 'pscw-border-color-table',
                            onChange: function (event, ui) {
                                let color = ui.color.toString();
                                _this.transmitData( "changeBorderColor", {
                                    id: _this.selectedEditEle?.id,
                                    borderColor: color,
                                } );
                                elementsSave[_this.selectedEditEle.id].borderColor = color;
                            },
                        });

                        let cellTextSize = new PscwComponent( {
                            label: i18n.cell_text_size,
                            type: 'upgrade',
                        } );

                        let horizontalBorderWidth = new PscwComponent({
                            label: i18n.horizontal_border_width,
                            type: 'inputNumber',
                            value: _this.selectedEditEle.horizontalBorderWidth,
                            id: 'pscw-horizontal-border-width-table',
                            onInput: function (e) {
                                let value = $( this ).val();
                                if ( value === '' || parseFloat( value ) < 0 ) {
                                    $( this ).val( '' );
                                }
                                _this.transmitData( "changeBorderWidth", {id: _this.selectedEditEle.id, horizontalBorderWidth: value, verticalBorderWidth: _this.selectedEditEle.verticalBorderWidth } );
                                elementsSave[_this.selectedEditEle.id].horizontalBorderWidth = value;
                            },
                        });

                        let horizontalBorderStyle = new PscwComponent({
                            label: i18n.horizontal_border_style,
                            type: 'select',
                            id: 'pscw-horizontal-border-style-table',
                            value: _this.selectedEditEle.horizontalBorderStyle,
                            choices: _this.borderOptions,
                            onChange: function (e) {
                                let value = $( this ).val();
                                _this.transmitData( "changeTableBorderStyle", {id: _this.selectedEditEle.id, horizontalBorderStyle: value, verticalBorderStyle: _this.selectedEditEle.verticalBorderStyle } );
                                elementsSave[_this.selectedEditEle.id].horizontalBorderStyle = value;
                            }
                        });

                        let verticalBorderWidth = new PscwComponent({
                            label: i18n.vertical_border_width,
                            type: 'inputNumber',
                            value: _this.selectedEditEle.verticalBorderWidth,
                            id: 'pscw-vertical-border-width-table',
                            onInput: function (e) {
                                let value = $( this ).val();
                                if ( value === '' || parseFloat( value ) < 0 ) {
                                    $( this ).val( '' );
                                }
                                _this.transmitData( "changeBorderWidth", {id: _this.selectedEditEle.id, horizontalBorderWidth: _this.selectedEditEle.horizontalBorderWidth, verticalBorderWidth: value } );
                                elementsSave[_this.selectedEditEle.id].verticalBorderWidth = value;
                            },
                        });

                        let verticalBorderStyle = new PscwComponent({
                            label: i18n.vertical_border_style,
                            type: 'select',
                            id: 'pscw-vertical-border-style-table',
                            value: _this.selectedEditEle.verticalBorderStyle,
                            choices: _this.borderOptions,
                            onChange: function (e) {
                                let value = $( this ).val();
                                _this.transmitData( "changeTableBorderStyle", {id: _this.selectedEditEle.id, verticalBorderStyle: value, horizontalBorderStyle: _this.selectedEditEle.horizontalBorderStyle } );
                                elementsSave[_this.selectedEditEle.id].verticalBorderStyle = value;
                            }
                        });

                        let tableMargin = new PscwComponent({
                            label: i18n.margin,
                            value: _this.selectedEditEle.margin || [0,0,0,0],
                            type: 'fourDimensional',
                            id: 'pscw-margin-table',
                            onChange: function (data) {
                                _this.transmitData( "changeTableMargin", {id: _this.selectedEditEle?.id, margin: data } );
                                elementsSave[_this.selectedEditEle.id].margin = data;
                            }
                        });

                        let tableBorderRadius = new PscwComponent({
                            label: i18n.border_radius,
                            value: _this.selectedEditEle.borderRadius || [0,0,0,0],
                            type: 'fourDimensional',
                            id: 'pscw-border-radius-table',
                            onChange: function (data) {
                                _this.transmitData( "changeTableBorderRadius", {
                                    id: _this.selectedEditEle?.id,
                                    borderRadius: data,
                                } );
                                elementsSave[_this.selectedEditEle.id].borderRadius = data;
                            }
                        });

                        let tableMaxHeight = new PscwComponent( {
                            label: i18n.max_height_px,
                            type: 'upgrade',
                        } );

                        let headerTable = new PscwComponent( {
                            label: i18n.header_table,
                            type: 'radio',
                            value: 'row',
                            choices: {
                                row: i18n.row_header,
                                column: i18n.column_header + ' (Premium)',
                                both: i18n.both_header + ' (Premium)',
                            },
                            id: 'pscw-table-header',
                            onInput: function (e) {
                                let value = 'row';
                                _this.transmitData( "changeHeaderColumn", Object.assign( {}, _this.selectedEditEle, { headerColumn : value, } ) );
                                elementsSave[_this.selectedEditEle.id].headerColumn = value;
                            },
                        } );

                        let templateColor = new PscwComponent( {
                            label: i18n.template_color,
                            type: 'templateColor',
                            value: '',
                            choices: _this.templateColors,
                            id: 'pscw-table-template-color',
                            onClick: function (e) {
                                let color = _this.templateColors[$( e.target ).data("color")];
                                if ( color?.header ) {
                                    $( "#customize-color-picker-pscw-header-background-table" ).wpColorPicker( 'color', color?.header );
                                }
                                if ( color?.even ) {
                                    $( "#customize-color-picker-pscw-even-background-table" ).wpColorPicker( 'color', color?.even );
                                }
                                if ( color?.odd ) {
                                    $( "#customize-color-picker-pscw-odd-background-table" ).wpColorPicker( 'color', color?.odd );
                                }
                                if ( color?.textHeader ) {
                                    $( "#customize-color-picker-pscw-text-header-table" ).wpColorPicker( 'color', color?.textHeader );
                                }
                                if ( color?.border ) {
                                    $( "#customize-color-picker-pscw-border-color-table" ).wpColorPicker( 'color', color?.border );
                                }
                                if( color?.headerTextBold ) {
                                    $( "#customize-input-pscw-header-text-bold-table" ).prop('checked', true).trigger('change');
                                }
                            },
                        } );

                        let btnImportCSV = new PscwComponent( {
                            type: 'btnUpload',
                            value: i18n.import_csv,
                            id: 'pscw-table-import-csv',
                            onChange: function (e) {
                                let file = this.files[0];
                                /*Check file extension*/
                                let ext = file['name'].split('.').pop().toLowerCase();
                                if ( ext !== 'csv' ) {
                                    alert(i18n.required_file_csv);
                                }else {


                                    let reader = new FileReader();
                                    reader.readAsText( file );

                                    //When the file finish load
                                    reader.onload = function ( event ) {
                                        let csv = event.target.result;
                                        let arr = csv.split('\n');

                                        if ( arr.length > 0 ) {
                                            let confirm_import = confirm(i18n.confirm_replace_csv);
                                            if ( confirm_import ) {
                                                _this.transmitData( "importCSV", Object.assign( {}, _this.selectedEditEle, {csv: arr} ) );
                                            }else {
                                                $( this ).val( '' );
                                            }

                                        }else {
                                            alert(i18n.alert_csv_empty);
                                        }
                                    }
                                }
                            }
                        } );

                        container.append( templateColor.render() );
                        container.append( addColumns.render() );
                        container.append( addRows.render() );
                        container.append( headerTable.render() );
                        container.append( headerBackground.render() );
                        container.append( textHeader.render() );
                        container.append( headerTextBold.render() );
                        container.append( headerTextSize.render() );
                        container.append( columnsStyle.render() );
                        container.append( evenBackground.render() );
                        container.append( evenText.render() );
                        container.append( oddBackground.render() );
                        container.append( oddText.render() );
                        container.append( borderColor.render() );
                        container.append( cellTextSize.render() );
                        container.append( horizontalBorderWidth.render() );
                        container.append( horizontalBorderStyle.render() );
                        container.append( verticalBorderWidth.render() );
                        container.append( verticalBorderStyle.render() );
                        container.append( tableBorderRadius.render() );
                        container.append( tableMargin.render() );
                        container.append( tableMaxHeight.render() );
                        container.append( btnImportCSV.render() );

                    }else {
                        container.children().not(".customize-section-description-container").remove();
                        _this.selectedContainerType = 'container';
                    }
                });
            });
        },

        addColumns(e) {
            const _this = e.data.self;
            let columns = $("#pscw-add-columns-table").val();
            _this.transmitData( "addColumns", Object.assign( {}, _this.selectedEditEle, {columns: columns} ) );
        },

        addRows(e) {
            const _this = e.data.self;
            let rows = $("#pscw-add-rows-table").val();
            _this.transmitData( "addRows", Object.assign( {}, _this.selectedEditEle, {rows: rows} ) );
        },

        removeColumns(e) {
            const _this = e.data.self;
            let columns = $("#pscw-add-columns-table").val();
            _this.transmitData( "removeColumns", {id: _this.selectedEditEle.id, columns: columns} );
        },

        removeRows(e) {
            const _this = e.data.self;
            let rows = $("#pscw-add-rows-table").val();
            _this.transmitData( "removeRows", {id: _this.selectedEditEle.id, rows: rows} );
        },


        handleUploadImage( data, action ) {
            const _this = this;
            const uploadImage = $( `#${data?.id}` );
            let globalSrc = data?.value?.src,
                globalAlt = data?.value?.alt;
            uploadImage.on( "click", function (e) {
                const imageWrap = uploadImage.find( ".pscw-customize-image-wrap" ),
                    image = uploadImage.find( ".pscw-customize-image" ),
                    imageURL = uploadImage.find( ".pscw-customize-image-url" ),
                    imageAlt = uploadImage.find( ".pscw-customize-image-alt" );
                let selectedEle = e.target;
                switch ( selectedEle.classList[0] ) {
                    case 'pscw-customize-image-wrap':
                    case 'pscw-customize-image':
                        let mediaUploader = wp.media( {
                            title: "Choose Image",
                            button: {
                                text: "Select",
                            },
                            multiple: false,
                        } );

                        mediaUploader.on( "select", function (){

                            let attachment = mediaUploader.state().get( "selection" ).first().toJSON();

                            if ( 0 > attachment.url.trim().length ) {
                                return;
                            }
                            imageWrap.addClass( "remove" );
                            image.attr( "src", attachment.url );
                            imageURL.val( attachment.url );
                            imageAlt.val( attachment.alt );
                            globalSrc = attachment.url;
                            globalAlt = attachment.alt;
                            _this.transmitData( action, {id: _this.selectedEditEle?.id, value: { url: attachment.url, alt: attachment.alt } } );
                            elementsSave[_this.selectedEditEle.id].src = globalSrc;
                            elementsSave[_this.selectedEditEle.id].alt = globalAlt;
                        } );

                        mediaUploader.open();
                        break;
                    case 'pscw-customize-image-remove':
                        imageWrap.removeClass( "remove" );
                        image.attr( "src", VicPscwParams.placeholderImage );
                        image.attr( "alt", "" );
                        imageURL.val("");
                        imageAlt.val("");
                        globalSrc = VicPscwParams.placeholderImage;
                        globalAlt = "";
                        _this.transmitData( action, {id: _this.selectedEditEle?.id, value: { url: VicPscwParams.placeholderImage, alt: "" } } );
                        elementsSave[_this.selectedEditEle.id].src = globalSrc;
                        elementsSave[_this.selectedEditEle.id].alt = globalAlt;
                        break;
                }
            } );

            uploadImage.on( "input", '.pscw-customize-image-url', function () {
                let src = $(this).val();
                globalSrc = src;
                uploadImage.find( ".pscw-customize-image" ).attr( "src", src );
                _this.transmitData( action, {id: _this.selectedEditEle?.id, value: { url: src, alt: globalAlt } } );
                elementsSave[_this.selectedEditEle.id].src = globalSrc;
                elementsSave[_this.selectedEditEle.id].alt = globalAlt;
            } );

            uploadImage.on( "input", '.pscw-customize-image-alt', function () {
                let alt = $(this).val();
                globalAlt = alt;
                uploadImage.find( ".pscw-customize-image" ).attr( "alt", alt );
                _this.transmitData( action, {id: _this.selectedEditEle?.id, value: { url: globalSrc, alt: alt } } );
                elementsSave[_this.selectedEditEle.id].src = globalSrc;
                elementsSave[_this.selectedEditEle.id].alt = globalAlt;
            } );
        },

        handleRangeSlider( data, action, valueChange, valueUnitChange ) {
            const _this = this;
            const setOnChangeForInput = ( rangeValue, min, max, range ) => {
                rangeValue.off('change');
                rangeValue.on('change', function () {
                    let val = parseInt($(this).val() || 0);
                    if (val > parseInt(max)) {
                        val = max;
                    } else if (val < parseInt(min)) {
                        val = min;
                    }
                    range.range('set value', val);
                });
            };

            let range_container = $( `#customize-control-${data.id}` );
            let range_wrap = range_container.find(".pscw-customize-range"),
                range = range_container.find(".pscw-customize-range1"),
                range_value = range_wrap.find(".pscw-customize-range-value"),
                range_unit = range_wrap.find(".pscw-customize-unit");

            let selectedUnit = data?.value?.data?.unit;
            let min = data?.value?.unit[selectedUnit]?.min || 0,
                max = data?.value?.unit[selectedUnit]?.max || 0,
                start = data?.value?.data?.value || 0,
                step = data?.value?.unit[selectedUnit]?.step || 1;
            let initStart = 0,
                currentVal = start,
                currentUnit = selectedUnit;
            range.range({
                min: min,
                max: max,
                start: start,
                step: step,
                input: range_value,
                onChange: function (val) {
                    if ( initStart > 0 ) {
                        currentVal = val;
                        let data = {};
                        data.id = _this.selectedEditEle?.id;
                        data[valueChange] = currentVal;
                        data[valueUnitChange] = currentUnit;

                        _this.transmitData( action, data );
                        elementsSave[_this.selectedEditEle?.id][valueChange] = currentVal;
                        elementsSave[_this.selectedEditEle?.id][valueUnitChange] = currentUnit;
                    }
                    initStart = 1;
                }
            });

            range_unit.on( 'change', function () {
                let selectedOption = $(this).find('option:selected');
                let selectedOptionData = selectedOption.data( "value" );
                currentUnit = selectedOption.text();
                range.range({
                    min: selectedOptionData[0],
                    start: selectedOptionData[0],
                    step: selectedOptionData[1],
                    max: selectedOptionData[2],
                    input: range_value,
                    onChange: function (val) {
                        currentVal = val
                        let data = {};
                        data.id = _this.selectedEditEle?.id;
                        data[valueChange] = currentVal;
                        data[valueUnitChange] = currentUnit;

                        _this.transmitData( action, data );
                        elementsSave[_this.selectedEditEle?.id][valueChange] = currentVal;
                        elementsSave[_this.selectedEditEle?.id][valueUnitChange] = currentUnit;
                    }
                });

                setOnChangeForInput(range_value,selectedOptionData[0],selectedOptionData[2],range);
            } );

            setOnChangeForInput(range_value,min,max,range);
        },

        handleSortAble() {
            const _this = this;
            let start = -1,
                stop = -1,
                move = false,
                parentIdStart = '',
                parentIdStop = '';
            $( ".pscw-customize-list-component" ).sortable({
                connectWith: ".pscw-connectedSortable",
                placeholder: "pscw-customize-component-place-holder",
                start: function ( event, ui ) {
                    parentIdStart = ui.item.closest( ".pscw-customize-col" ).attr("id");
                    start = $( `#${parentIdStart} .pscw-customize-list-component .pscw-customize-component` ).index(ui.item);
                },
                receive: function () {
                    move = true;
                },
                stop: function ( event, ui ) {
                    const item = ui.item,
                        itemId = item[0].id;
                    parentIdStop = item.closest( ".pscw-customize-col" ).attr("id");
                    stop = $( `#${parentIdStop} .pscw-customize-list-component .pscw-customize-component` ).index(ui.item);

                    let data = {};
                    let eleStart = elementsSave[parentIdStart].children[start];
                    let eleStop = elementsSave[parentIdStop].children[stop];
                    if ( move === true ) {
                        if ( item.prev().length > 0 ) {
                            let prevItem = item.prev(),
                                index = elementsSave[parentIdStop].children.indexOf( prevItem.attr("id") );

                            if ( index !== -1 ) {
                                elementsSave[parentIdStop].children.splice( index + 1, 0, itemId );
                            }

                            let oldIndex = elementsSave[elementsSave[itemId].parent].children.indexOf( itemId ) ;
                            if ( oldIndex !== -1 ) {
                                elementsSave[elementsSave[itemId].parent].children.splice( oldIndex, 1 );
                            }

                            elementsSave[itemId].parent = parentIdStop;

                            data = {
                                func:'after',
                                targetId: prevItem.attr("id"),
                                id: itemId,
                            }
                        }else if ( item.next().length > 0 ) {
                            let nextItem = item.next();

                            let index = elementsSave[parentIdStop].children.indexOf( nextItem.attr("id") );
                            if (index !== -1) {
                                elementsSave[parentIdStop].children.splice( index, 0, itemId );
                            }

                            let oldIndex = elementsSave[elementsSave[itemId].parent].children.indexOf( itemId ) ;
                            if (oldIndex !== -1) {
                                elementsSave[elementsSave[itemId].parent].children.splice( oldIndex, 1 );
                            }

                            elementsSave[itemId].parent = parentIdStop;
                            data = {
                                func:'before',
                                targetId: nextItem.attr("id"),
                                id: itemId,
                            }
                        }else {
                            let oldIndex = elementsSave[elementsSave[itemId].parent].children.indexOf( itemId ) ;
                            if (oldIndex !== -1) {
                                elementsSave[elementsSave[itemId].parent].children.splice( oldIndex, 1 );
                            }
                            elementsSave[itemId].parent = parentIdStop;
                            elementsSave[parentIdStop].children = [itemId];

                            data = {
                                func:'append',
                                targetId: parentIdStop,
                                id: itemId,
                            }
                        }

                    }else {
                        if ( start > stop ) {
                            elementsSave[parentIdStart].children.splice(start, 1);
                            elementsSave[parentIdStart].children.splice(stop, 0, eleStart);
                            data = {
                                func:'before',
                                targetId: eleStop,
                                id: itemId,
                            }
                        }else {
                            if ( start + 1 === stop ) {
                                elementsSave[parentIdStart].children.splice(start, 1, eleStop);
                                elementsSave[parentIdStart].children.splice(stop, 1, eleStart);
                            } else {
                                elementsSave[parentIdStart].children.splice(start, 1);
                                elementsSave[parentIdStart].children.splice(stop, 0, eleStart);
                            }
                            data = {
                                func:'after',
                                targetId: eleStop,
                                id: itemId,
                            }
                        }
                    }
                    move = false;
                    parentIdStart = '';
                    parentIdStop = '';
                    start = -1;
                    stop = -1;
                    _this.transmitData("updateRow", data );
                }
            }).disableSelection();
        },

        addRow(e) {
            const _this = e.data.self, rowId = 'pscw-row-' + pscwGenerateID();
            let html = [],
                colsId = [],
                dataCols = $( this ).data("cols").toString().split(","),
                numberCol = dataCols.length,
                containerId = '',
                colsData = {};

            for (let i = 0; i < numberCol ; ++i) {
                let colId = 'pscw-col-' + pscwGenerateID();
                let colData = {
                    id: colId,
                    class: `pscw-col-l-${dataCols[i]}`,
                    type: "column",
                    parent: rowId,
                    children: [],
                    settings: {
                        class: `pscw-customize-col-${dataCols[i]}`,
                    }
                }
                elementsSave[ colId ] = colData;
                colsData[colId] = colData;

                colsId.push( colId );
                html.push(`<div class="pscw-customize-col pscw-customize-col-${dataCols[i]}" id="${colId}">
                <div class="pscw-customize-list-component pscw-connectedSortable">
                </div>
                <div class="pscw-customize-open-tab-component">&#65291;</div>
                </div>`)
            }

            $( ".pscw-customize-container" ).append( `<div class="pscw-customize-row" id="${rowId}"><span class="pscw-customize-row-remove">&#x2715;</span>${html.join("")}</div>` );
            layoutSave.children.push(rowId);

            elementsSave[rowId] = { children: colsId, id: rowId, type: 'row' };

            _this.handleSortAble();
            _this.transmitData( 'addRow', {
                id: rowId,
                type: 'row',
                containerType: _this.selectedContainerType,
                containerId: containerId,
                colsData: colsData,
            } );
        },

        removeRow(e) {
            const _this = e.data.self;
            let id = $( this ).parent().attr("id"),
                descendantIds = _this.getIdDescendantElements( id, elementsSave ),
                index = '';

            index = layoutSave.children.indexOf( id );
            layoutSave.children.splice( index, 1 );

            for (const descendantId of descendantIds) {
                delete elementsSave[descendantId];
            }
            delete elementsSave[id];

            $( this ).parent().remove();
            _this.transmitData( "removeRow", {id} );
        },

        transmitData( action, data, isChange = true ) {
            if ( isChange ) {
                api('woo_sc_setting[cus_design]').set(!api('woo_sc_setting[cus_design]').get());
            }
            if ( action.length > 0 ) {
                api.previewer.send("pscw_change_value", {
                    action,
                    data
                } );
            }
        },

        openComponentPanel(e) {
            const _this = e.data.self;
            if ( _this.selectedCol !== null ) {
                _this.selectedCol.removeClass( "selected" );
            }
            _this.selectedCol = $( this ).parent();
            _this.selectedCol.addClass( "selected" );
            $(document.body).addClass( "outer-section-open" );
            $( "#pscw-customize-component-panel" ).addClass("open");
        },

        closeComponentPanel(e) {
            const _this = e.data.self;
            if ( _this.selectedCol ) {
                _this.selectedCol.removeClass( "selected" );
                _this.selectedCol = null;
            }
            $(document.body).removeClass( "outer-section-open" );
            $( "#pscw-customize-component-panel" ).removeClass("open");
        },

        refreshComponentPanel(e) {
            const _this = e.data.self;
            $( "#pscw-customize-component-panel" ).removeClass("open");
            if ( _this.selectedCol ) {
                _this.selectedCol.removeClass( "selected" );
                _this.selectedCol = null;
            }
        },

        addComponent(e){
            const _this = e.data.self;
            if ( _this.selectedCol !== null ) {
                let type = $( this ).data( "component" ),
                    id = `pscw-${type}-${pscwGenerateID()}`,
                    parentId = $( _this.selectedCol ).attr( "id" ),
                    subData = [];
                for (const elementsSaveKey in elementsSave) {
                    let typeSaveKey = elementsSave[elementsSaveKey].type;
                    if ( type === typeSaveKey ) {
                        return;
                    }
                }
                let data =  {
                    image: {
                        alt: "",
                        borderColor: "#000000",
                        borderStyle: "solid",
                        borderWidth: 0,
                        height: 100,
                        heightUnit: "%",
                        width: 100,
                        widthUnit: "%",
                        src: VicPscwParams.placeholderImage,
                        padding: [0,0,0,0],
                        margin: [0,0,0,0],
                        objectFit: 'unset'
                    },
                    table: {
                        columns: [""],
                        rows: [[""]],
                        headerColumn: 'row',
                        headerBackground: '#ffffff',
                        textHeader : '#000000',
                        headerTextBold : true,
                        headerTextSize : 14,
                        columnsStyle : false,
                        evenBackground : '#ffffff',
                        evenText : '#494949',
                        oddBackground : '#ffffff',
                        oddText : '#494949',
                        borderColor : '#9D9D9D',
                        cellTextSize : 14,
                        horizontalBorderWidth : 1,
                        horizontalBorderStyle : 'solid',
                        verticalBorderWidth : 1,
                        verticalBorderStyle : 'solid',
                        margin:[0,0,0,0],
                        borderRadius:[0,0,0,0],
                        tableMaxHeight: 535,
                    },
                    text: {
                        value: i18n.add_your_text,
                        margin:[0,0,0,0],
                    }

                }

                if ( ! data[type]  ) {
                    return;
                }

                elementsSave[id] = Object.assign( {}, {id, type, parent: parentId,}, data[type] );
                ViPscw.CustomizeSettings.changeComponentStatus();
                if ( subData.length > 0 ) {
                    data[type].subData = subData;
                }

                let html = `<div class="pscw-customize-component" id="${id}">
                         ${type.charAt(0).toUpperCase() + type.slice(1)}
                         <div class="pscw-customize-component-action pscw-customize-component-action__edit">&#9998;</div>
                         <div class="pscw-customize-component-action pscw-customize-component-action__remove">&#x2715;</div>
                    </div>`;
                $( _this.selectedCol ).find( ".pscw-customize-list-component" ).append( html );
                _this.transmitData( "addComponent", Object.assign( {}, {id, type, parent: parentId,}, data[type] ) );
                elementsSave[parentId].children.push(id);
                _this.selectedCol.removeClass( "selected" );
                _this.selectedCol = null;
                $(document.body).removeClass( "outer-section-open" );
                $( "#pscw-customize-component-panel" ).removeClass("open");
            }
        },

        getIdDescendantElements( ancestorId, elements ) {
            const _this = this;
            if (typeof elements[ancestorId] === 'undefined') {
                return '';
            }
            const ancestor = elements[ancestorId];
            let ids = [];
            let componentsId = [];
            switch ( ancestor.type ) {
                case 'row':
                    for (const childId of ancestor.children) {
                        ids.push( childId );
                        componentsId.push( ..._this.getIdDescendantElements( childId,elements ) )
                    }
                    if ( componentsId.length > 0 ) {
                        ids.push( ...componentsId );
                    }
                    break;
                case 'column':
                    for (const childId of ancestor.children) {
                        ids.push( childId );
                        componentsId.push( ..._this.getIdDescendantElements( childId,elements ) )
                    }
                    if ( componentsId.length > 0 ){
                        ids.push(...componentsId);
                    }
                    break;
            }
            return ids;
        },

        removeComponent(e) {
            e.stopPropagation();
            const _this = e.data.self;
            let removeEle = $( this ).parent(),
                id = removeEle.attr("id"),
                parentId = elementsSave[id].parent,
                index = elementsSave[parentId].children.indexOf( id );

            if ( index !== -1 ) {
                let descendantIds = _this.getIdDescendantElements(id, elementsSave);
                elementsSave[parentId].children.splice( index, 1 );
                for (const descendantId of descendantIds) {
                    delete elementsSave[descendantId];
                }
                _this.transmitData( "removeComponent", { id } );
                removeEle.remove();
                delete elementsSave[id];
            }
            ViPscw.CustomizeSettings.changeComponentStatus();
        },

        openEditComponent(e) {
            e.stopPropagation();
            const _this = e.data.self;
            let id = $(this).parent().attr( "id" );

            _this.selectedEditEle = elementsSave[id];
            _this.handleDataForOpenEditComponent();

        },

        handleDataForOpenEditComponent( isTransmit = true ) {
            const _this = this;

            if ( isTransmit ) {
                _this.transmitData( "editComponent", {id : _this.selectedEditEle.id, containerType: _this.selectedContainerType}, false );
            }

            switch ( _this.selectedEditEle?.type ) {
                case "divider":
                    api.section("pscw_customizer_divider").expanded(true);
                    break;
                case "accordion":
                    api.section("pscw_customizer_accordion").expanded(true);
                    break;
                case "tab":
                    api.section("pscw_customizer_tab").expanded(true);
                    break;
                case "text":
                    api.section("pscw_customizer_text").expanded(true);
                    break;
                case "table":
                    api.section("pscw_customizer_table").expanded(true);
                    break;
                case "image":
                    api.section("pscw_customizer_image").expanded(true);
                    break;
            }
        },

        changeComponentStatus() {
            const typesExist = Object.values(elementsSave).map(item => item.type);
            $('.pscw-customize-component-panel-item').each( function() {
                const type = $(this).data('component');
                if ( typesExist.includes(type) ) {
                    $(this).addClass('pscw-disabled');
                }else {
                    $(this).removeClass('pscw-disabled');
                }
            })

        }
    }

    ViPscw.CustomizeSettings.init();
});