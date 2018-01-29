<template>
    <div>
        <div v-if="errors">
            <div class="alert alert-danger">
                <p v-for="error in errors" >
                    {{ error[0] }}
                </p>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h3>Migration Settings</h3>

                <div class="form-group">
                    <input type="text" class="form-control" name="migration_name"
                           placeholder="Type the name of your migration here" v-model="migration_name">
                    <p class="form-text">Example: create users table, the file name and class naming conventions will be
                        applied for you</p>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="table_name" placeholder="MySQL table name"
                           v-model="table_name">
                </div>
                <div class="form-group">
                    <p>Migration Type:</p>

                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" class="form-check-input" name="migration_type" value="create" v-model="migration_type" checked>
                            Create (default) - if you want this migration to create a new database table
                        </label>
                    </div>
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="radio" class="form-check-input" name="migration_type" value="modify" v-model="migration_type">
                            Modify - choose this option to modify an existing table
                        </label>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <h3>Columns</h3>
                <div class="alert alert-info" v-if="columns.length === 0">
                    Click the add column button below to get started
                </div>
                <div class="columns-list">
                    <column
                        v-for="(column, columnIndex) in columns"
                        :column="column"
                        :handleRemoveColumn="removeColumn.bind(this, columnIndex)"
                        :index="columnIndex"
                        :key="columnIndex">
                    </column>
                </div>
                <div class="text-right">
                    <button @click.prevent="addColumn" class="btn btn-default">
                        <span class="fa fa-plus-circle"></span> Add Column
                    </button>
                    <button @click.prevent="sendColumns" class="btn btn-primary" :disabled="columns.length === 0">
                        Generate Migration
                    </button>
                </div>
            </div>
        </div>

        <div class="clearfix"></div>

        <div v-if="code">
            <h2>{{ file_name }}
                <div class="buttons">
                    <a class="btn btn-default" :href="fileContent" :download="file_name">
                        <i class="fa fa-download"></i>
                        Download
                    </a>
                    <button class="btn btn-primary" @click.prevent v-clipboard:copy="code">
                        <i class="fa fa-clipboard"></i>
                        Copy to clipboard
                    </button>
                </div>
            </h2>
            <div class="form-group">
                <pre>
                    <code class="php" ref="code">{{ code }}</code>
                </pre>
            </div>
        </div>
    </div>
</template>

<script>
    import hljs from 'highlight.js';

    export default {
        name: "migration-columns",

        data() {
            return {
                columns: [],
                migration_type: null,
                migration_name: null,
                table_name: null,
                errors: null,
                loading: false,
                // the generated code
                code: null,
                // migration's generated file name
                file_name: null,
            };
        },

        computed: {
            fileContent() {
                return 'data:application/octet-stream,' + encodeURI(this.code);
            }
        },

        methods: {
            addColumn() {
                // initialise the column and its fields
                // default type is text
                let column = {
                    name: null,
                    type: 'integer',
                    nullable: false,
                    default: null,
                    // type-specific properties
                    unsigned: false,
                    // array of options, e.g. enum
                    options: [],
                    // input length, e.g. VARCHAR column
                    length: null,
                    // how many decimal digits can a float/decimal/double number have
                    scale: null,
                    // how many digits can a float/decimal/double number have in total
                    precision: null,
                    is_foreign_key: false,
                    foreign_key: {
                        references: null,
                        on_delete: 'restrict',
                        on_update: 'restrict'
                    }
                };

                this.columns.push(column);

                // trigger an event to reload invalid input handler for the new fields
                this.$nextTick(() => {
                    $(document).trigger('reloadInvalidInputHandler');
                });
            },

            removeColumn(columnIndex) {
                this.columns.splice(columnIndex, 1)
            },

            sendColumns() {
                if (this.loading) {
                    return;
                }
                // clear the code property first
                this.code = "";
                this.loading = true;

                axios
                    .post('api/generate', {
                        columns: this.columns,
                        migration_name: this.migration_name,
                        table_name: this.table_name,
                        migration_type: this.migration_type
                    })
                    .then((response) => {
                        this.code = response.data.code;
                        this.file_name = response.data.file_name;
                        this.errors = null;
                        this.loading = false;
                    })
                    .catch((error) => {
                        this.loading = false;

                        if (error.response) {
                            this.errors = error.response.data.errors;
                            this.triggerErrors(error.response.data.errors);
                        }
                    });
            },

            triggerErrors(errors) {
                console.log(errors);
                Object.keys(errors).forEach((error) => {
                    $(`input[name='${error}']`).trigger('invalid');
                });
            },
        },

        watch: {
            code(value) {
                if (!value) {
                    return;
                }

                this.$nextTick(() => {
                    hljs.highlightBlock(this.$refs.code);
                });
            }
        }
    }
</script>

<style scoped lang="scss">
    h2 {
        &:after {
            clear: both;
            display: block;
            content: '';
        }
    }

    pre {
        font-size: 0;
        padding: 0;

        code {
            font-size: 14px;
        }
    }

    .buttons {
        display: inline-block;
        float: right;
    }

    .columns-list {
        margin-bottom: 20px;
    }
</style>