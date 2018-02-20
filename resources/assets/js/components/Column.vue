<template>
    <div class="migration-column" ref="column">
        <div class="column-header" :data-target="`#column-body-${index}`" data-toggle="collapse" ref="header">
            <h4>Column {{ columnName }}, type: {{ column.type }}</h4>
            <button class="btn-sm btn-danger" @click.prevent="removeColumn">
                <span class="fa fa-times"></span>
            </button>
        </div>

        <div class="collapse in" :id="`column-body-${index}`">
            <div class="column-body">
                <div class="form-group" v-if="canHaveName">
                    <label>Column name:</label>
                    <input type="text" :name="inputName('name')" class="form-control" v-model="column.name">
                </div>

                <div class="form-group" v-if="canHavePrecision">
                    <label>Precision:</label>
                    <p>How many digits can a number have in total?</p>
                    <input type="number" :name="inputName('precision')" class="form-control" v-model="column.precision" />
                </div>

                <div class="form-group" v-if="canHaveScale">
                    <label>Scale:</label>
                    <p>How many decimal digits can a number have?</p>
                    <input type="number" :name="inputName('scale')" class="form-control" v-model="column.scale" />
                </div>

                <div class="form-group" v-if="canHaveLength">
                    <label>Length:</label>
                    <input type="number" :name="inputName('length')" class="form-control" v-model="column.length" />
                </div>

                <div class="form-group" v-if="canHaveDefaultValue">
                    <label>Default Value:</label>
                    <template v-if="allowedValues instanceof Array">
                        <select class="form-control" v-model="column.default">
                            <option v-for="(option, index) in allowedValues" :value="option.value">{{ option.name }}</option>
                        </select>
                    </template>
                    <input v-else type="text" :name="inputName('default')" class="form-control" v-model="column.default" />
                </div>

                <div class="form-group" v-if="canHaveOptions">
                    <label>Options:</label>
                    <ul class="column-options" v-if="column.allowed_values.length">
                        <li v-for="(option, index) in column.allowed_values">
                            {{ option }}
                            <button class="pull-right btn-sm btn-danger" @click.prevent="removeColumnOption(index)">
                                <span class="fa fa-times"></span> Remove
                            </button>
                        </li>
                    </ul>
                    <div class="input-group">
                        <input type="text" @keyup.delete="handleColumnOptionBackspace" @keyup.enter="addColumnOption" class="form-control" placeholder="Add new option..." ref="optionInput" />
                        <span class="input-group-btn">
                            <button class="btn btn-primary" @click.prevent="addColumnOption">Add</button>
                        </span>
                    </div>
                </div>

                <div class="form-group">
                    <label>Type:</label>
                    <select :name="inputName('type')" class="form-control" v-model="column.type">
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
                    <div class="column-foreign-key">
                        <div class="form-group">
                            <label>References:</label>
                            <input type="text" class="form-control" name="references" placeholder="MySQL Table name"
                                   v-model="column.foreign_key.references">
                        </div>

                        <div class="form-group row">
                            <div>
                                <label>On Delete:</label>
                                <select name="on_delete" class="form-control" v-model="column.foreign_key.on_delete">
                                    <option v-for="cascade in cascades" :value="cascade">{{ cascade }}</option>
                                </select>
                            </div>
                            <div>
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
    </div>
</template>

<script>
    import { columns, cascades } from '../modules/DatabaseConfig';

    export default {

        name: 'column',

        props: ['column', 'handleRemoveColumn', 'index'],

        data() {
            return {
                types: columns,
                cascades: cascades,
            };
        },

        computed: {
            columnName() {
                return this.column.name ? this.column.name : this.index + 1;
            },

            /* TYPE-SPECIFIC methods */
            canHaveName() {
                return this.column.type !== 'timestamps';
            },

            canHaveDefaultValue() {
                return this.column.type !== 'timestamps'
                    && this.column.type !== 'increments';
            },

            canHaveForeignKey() {
                return this.column.type === 'integer'
                    || this.column.type === 'increments'
                    || this.column.type === 'string';
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

                    return enumArray.concat(this.column.allowed_values.map((option) => {
                        return {
                            name: option,
                            value: option,
                        }
                    }));
                }

                return false;
            },
        },

        mounted() {
            window.bus.$on('migrationErrors', (errors) => {
                // check if there was an error with the fields
                let columnHasError = Object.keys(errors).find((key) => {
                    const regexp = new RegExp(`columns.${this.index}.*`);
                    const matchArray = key.match(regexp);

                    return matchArray ? matchArray[0] : null;
                });

                if (columnHasError) {
                    // we need to add 'with-error' class instead of 'has-error'
                    // thanks for that, bootstrap
                    this.$refs.column.classList.add('with-error');

                    // collapse the column which has an error
                    if ($(this.$refs.header).hasClass('collapsed')) {
                        $(this.$refs.header).trigger('click');
                    }
                }
            });
        },

        methods: {
            removeColumn() {
                this.handleRemoveColumn(this.index);
            },

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
                this.column.allowed_values.push(input.value);

                // reset the input value
                input.value = '';
                // re-focus on the input
                input.focus();
            },

            handleColumnOptionBackspace(event) {
                // if there are no allowed values, ignore this event
                if (!this.column.allowed_values) {
                    return;
                }

                // if the pressed key isn't backspace, ignore this event
                if (event.key !== "Backspace") {
                    return;
                }

                // if the input isn't empty, ignore this event
                if (this.$refs.optionInput.value !== "") {
                    return;
                }

                // if all of the above are not met, remove the latest element from the array
                this.column.allowed_values.pop();
            },

            removeColumnOption(index) {
                this.column.allowed_values.splice(index, 1);
            }
        },

        watch: {
            'column': {
                handler: function() {
                    this.$refs.column.classList.remove('with-error');
                },
                deep: true,
            },

            'column.type': {
                // when switching the column type, clear the column data
                handler: function(val) {
                    $(document).trigger('reloadInvalidInputHandler');

                    // with the exception of boolean type, which by default is only true/false and null is not expected
                    this.column.default = (val === 'boolean' ? true : null);
                    this.column.length = null;
                    this.column.precision = null;
                    this.column.scale = null;
                    this.column.nullable = false;
                    this.column.is_foreign_key = false;
                    this.column.unsigned = false;

                    if (this.canHaveOptions) {
                        this.column.allowed_values = [];
                    } else {
                        this.column.allowed_values = null;
                    }
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
    @import '~bootstrap-sass/assets/stylesheets/bootstrap/_variables.scss';
    @import '~bootstrap-sass/assets/stylesheets/bootstrap/mixins/_clearfix.scss';
    @import '~bootstrap-sass/assets/stylesheets/bootstrap/mixins/_grid.scss';

    .migration-column {
        margin-bottom: 8px;
        border-radius: 6px;
        border: 1px solid transparent;

        &.with-error {
            border-color: #e83619;

            .column-header, .column-body {
                box-shadow: none;
            }
        }

        .column-header {
            background: white;
            border-radius: 5px 5px 0 0;
            padding: 3px 14px;
            border: 1px solid #ccc;
            transition: border-radius 0.5s;
            cursor: pointer;
            position: relative;
            box-shadow: 0px 0px 5px rgba(68, 68, 68, 0.26);

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
            box-shadow: 0px 0px 5px rgba(68, 68, 68, 0.16);
            border: 1px solid #ccc;
            border-radius: 0 0 5px 5px;
            padding: 10px 14px;
            border-top: none;
        }
    }

    .column-foreign-key {
        background: #e7e9ec;
        border-radius: 3px;
        padding: 6px 10px;

        .row {
            // todo: globalise it
            @include make-row(10px);

            > div {
                @include make-sm-column(6, 10px);
            }
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