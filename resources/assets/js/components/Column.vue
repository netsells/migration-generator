<template>
    <div class="migration-column card">

        <div class="column-header" :data-target="'#column-body-' + index" data-toggle="collapse">
            <h4>Column {{ column.name ? column.name : index + 1 }}, type: {{ column.type }}</h4>
            <button class="btn-sm btn-danger" @click.prevent="handleRemoveColumn">
                <span class="fa fa-times"></span>
            </button>
        </div>

        <div class="collapse in" :id="'column-body-' + index">
            <div class="column-body">
                <div class="form-group" v-if="canHaveName">
                    <label>Column name:</label>
                    <input type="text" :name="inputName('name')" class="form-control" v-model="column.name">
                </div>

                <div class="form-group" v-if="canHavePrecision">
                    <label>Precision:</label>
                    <p>How many digits can a number have in total?</p>
                    <input type="text" class="form-control" v-model="column.precision" />
                </div>

                <div class="form-group" v-if="canHaveScale">
                    <label>Scale:</label>
                    <p>How many decimal digits can a number have?</p>
                    <input type="text" class="form-control" v-model="column.scale" />
                </div>

                <div class="form-group" v-if="canHaveLength">
                    <label>Length:</label>
                    <input type="number" class="form-control" v-model="column.length" />
                </div>

                <div class="form-group" v-if="canHaveDefaultValue">
                    <label>Default Value:</label>
                    <template v-if="allowedValues instanceof Array">
                        <select class="form-control" v-model="column.default">
                            <option v-for="(option, index) in allowedValues" :value="option.value">{{ option.name }}</option>
                        </select>
                    </template>
                    <input v-else type="text" class="form-control" v-model="column.default" />
                </div>

                <div class="form-group" v-if="canHaveOptions">
                    <label>Options:</label>
                    <ul class="column-options" v-if="column.options.length">
                        <li v-for="(option, index) in column.options">
                            {{ option }}
                            <button class="pull-right btn-sm btn-danger" @click.prevent="removeColumnOption(index)">
                                <span class="fa fa-times"></span> Remove
                            </button>
                        </li>
                    </ul>
                    <div class="input-group">
                        <input type="text" @keyup.enter="addColumnOption" class="form-control" placeholder="Add new option..." ref="optionInput" />
                        <span class="input-group-btn">
                            <button class="btn btn-primary" @click.prevent="addColumnOption">Add</button>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Type:</label>
                    <select name="type" class="form-control" v-model="column.type">
                        <option v-for="type in types" :value="type">{{ type }}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-check-label" v-if="isNullable">
                        <input type="checkbox" class="form-check-input" name="nullable" v-model="column.nullable">
                        Nullable
                    </label>

                    <label class="form-check-label" v-if="isColumnNumeric">
                        <input type="checkbox" class="form-check-input" name="unsigned" v-model="column.unsigned">
                        Unsigned
                    </label>

                    <label class="form-check-label" v-if="canHaveForeignKey">
                        <input type="checkbox" class="form-check-input" name="foreign_key"
                               v-model="column.is_foreign_key">
                        Foreign Key
                    </label>
                </div>

                <div v-if="column.is_foreign_key">
                    <div class="form-inline">
                        <div class="form-group">
                            <label>References:</label>
                            <input type="text" class="form-control" name="references" placeholder="MySQL Table name"
                                   v-model="column.foreign_key.references">
                        </div>

                        <div class="form-group">
                            <label>On Delete:</label>
                            <select name="on_delete" class="form-control" v-model="column.foreign_key.on_delete">
                                <option v-for="cascade in cascades" :value="cascade">{{ cascade }}</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>On Update:</label>
                            <select name="on_update" class="form-control" v-model="column.foreign_key.on_update">
                                <option v-for="cascade in cascades" :value="cascade">{{ cascade }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import columns from '../modules/ColumnTypes';

    export default {
        name: 'column',
        props: ['column', 'handleRemoveColumn', 'index'],

        data() {
            return {
                types: columns,
                cascades: ['restrict', 'cascade'],
            };
        },

        computed: {
            /* TYPE-SPECIFIC methods */
            canHaveName() {
                return this.column.type !== 'timestamps';
            },

            canHaveDefaultValue() {
                return this.column.type !== 'timestamps'
                    && this.column.type !== 'increments';
            },

            canHaveForeignKey() {
                return this.column.type !== 'timestamps'
                    && this.column.type !== 'enum';
            },

            canHaveLength() {
                return this.column.type === 'string';
            },

            canHavePrecision() {
                return this.column.type === 'float'
                    || this.column.type === 'double'
                    || this.column.type === 'decimal';
            },

            canHaveScale() {
                return this.column.type === 'float'
                    || this.column.type === 'double'
                    || this.column.type === 'decimal';
            },

            canHaveOptions() {
                return this.column.type === 'enum';
            },

            isColumnNumeric() {
                return this.column.type === 'float'
                    || this.column.type === 'double'
                    || this.column.type === 'decimal'
                    || this.column.type === 'integer';
            },

            isNullable() {
                // timestamps are nullable by default
                return this.column.type !== 'timestamps'
                    && this.column.type !== 'increments';
            },

            allowedValues() {
                if (this.column.type === 'boolean') {
                    let booleanValues = [];

                    if (this.column.nullable) {
                        booleanValues.push(
                            {
                                name: 'No default value',
                                value: null,
                            },
                        );
                    }

                    return booleanValues.concat([
                        {
                            name: 'True',
                            value: true,
                        },
                        {
                            name: 'False',
                            value: false,
                        }
                    ]);
                }

                if (this.column.type === 'enum') {
                    let enumArray = [
                        {
                            name: 'No default',
                            value: null,
                        },
                    ];

                    return enumArray.concat(this.column.options.map((option) => {
                        return {
                            name: option,
                            value: option,
                        }
                    }));
                }

                return false;
            },
        },

        methods: {
            inputName(name) {
                return 'columns.' + this.index + '.' + name;
            },

            addColumnOption() {
                const input = this.$refs.optionInput;

                // we don't want to add empty options
                if (input.value === '') {
                    return;
                }

                // add the option to the options array
                this.column.options.push(input.value);

                // reset the input value
                input.value = '';
                // re-focus on the input
                input.focus();
            },

            removeColumnOption(index) {
                this.column.options.splice(index, 1);
            }
        },

        watch: {
            'column.default': {
                handler: function(val) {
                    if (this.column.type === 'enum') {

                    }
                }
            },

            'column.type': {
                // when switching the column type, clear the type-specific data
                handler: function(val) {
                    $(document).trigger('reloadInvalidInputHandler');
                    // with the exception of boolean type, which by default is only true/false and null is not expected
                    this.column.default = (val === 'boolean' ? true : null);
                    this.column.options = [];
                    this.column.length = null;
                    this.column.precision = null;
                    this.column.scale = null;
                    this.column.nullable = false;

                    if (!this.canHaveName) {
                        this.column.name = null;
                    }

                    if (this.canHaveScale) {
                        this.column.scale = 2;
                    }

                    if (this.canHavePrecision) {
                        this.column.precision = 8;
                    }
                }
            },

            'column.nullable': {
                handler: function(val) {
                    if (this.column.type === 'boolean') {
                        this.column.default = val ? null : true;
                    }
                }
            }
        }
    }
</script>

<style lang="scss">
    .migration-column {
        margin-bottom: 8px;
        .column-header {
            background: white;
            border-radius: 5px 5px 0 0;
            padding: 3px 14px;
            border: 1px solid #ccc;
            transition: border-radius 0.5s;
            cursor: pointer;
            position: relative;

            &.collapsed {
                border-radius: 5px;
            }

            button {
                position: absolute;
                right: 8px;
                top: 8px;
            }
        }

        .column-body {
            border: 1px solid #ccc;
            border-radius: 0 0 5px 5px;
            padding: 10px 14px;
            border-top: none;
        }
    }

    ul.column-options {
        list-style: none;
        padding: 0;

        li {
            margin-bottom: 3px;
            padding: 5px 5px 5px 10px;
            background: #e7e9ec;
            line-height: 28px;

            &:after {
                clear: both;
                display: block;
                content: '';
            }
        }
    }
</style>