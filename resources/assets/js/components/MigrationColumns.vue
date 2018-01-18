<template>
    <div>
        <div v-if="errors">
            <div class="alert alert-danger">
                {{ errors.message }}
            </div>
        </div>

        <div class="pull-right">
            <button @click.prevent="addColumn" class="btn btn-default">
                <span class="fa fa-plus-circle"></span> Add Column
            </button>
            <button @click.prevent="sendColumns" class="btn btn-primary" :disabled="columns.length === 0">
                Generate Migration
            </button>
        </div>

        <div class="clearfix"></div>

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
                <div class="form-group" v-for="(column, columnIndex) in columns">
                    <button class="pull-right btn-sm btn-danger" @click.prevent="removeColumn(columnIndex)">
                        <span class="fa fa-times"></span> Remove Column
                    </button>

                    <div class="form-group">
                        <label>Column name:</label>
                        <input type="text" class="form-control" name="column_name" v-model="column.name">
                    </div>

                    <div class="form-group">
                        <label>Default Value:</label>
                        <input type="text" class="form-control" v-model="column.default">
                    </div>

                    <div class="form-group">
                        <label>Type:</label>
                        <select name="type" class="form-control" v-model="column.type">
                            <option v-for="type in mysql_types" :value="type">{{ type }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="nullable" v-model="column.nullable">
                            Nullable
                        </label>

                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="unsigned" v-model="column.unsigned">
                            Unsigned
                        </label>

                        <label class="form-check-label">
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

                    <hr>
                </div>
            </div>
        </div>

        <div v-if="code">
            <h2>{{ file_name }}</h2>
            <div class="form-group">
                <pre><code class="php">{{ code }}</code></pre>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: "migration-columns",

        data() {
            return {
                columns: [],
                cascades: ['restrict', 'cascade'],
                mysql_types: ['integer', 'string', 'text', 'enum', 'boolean', 'timestamps'],
                migration_type: null,
                migration_name: null,
                table_name: null,
                errors: null,
                // the generated code
                code: null,
                // migration's generated file name
                file_name: null,
            };
        },

        methods: {
            addColumn() {
                // I feel this should be a single column component
                let column = {
                    name: null,
                    type: 'text',
                    nullable: false,
                    unsigned: false,
                    is_foreign_key: false,
                    foreign_key: {
                        references: null,
                        on_delete: 'restrict',
                        on_update: 'restrict'
                    }
                };

                this.columns.push(column)
            },

            removeColumn(columnIndex) {
                this.columns.splice(columnIndex, 1)
            },

            sendColumns() {
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
                    })
                    .catch((error) => {
                        if (error.response) {
                            this.errors = error.response.data;
                        }
                    })
            }
        }
    }
</script>

<style scoped>

</style>