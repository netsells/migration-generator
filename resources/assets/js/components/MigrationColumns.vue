<template>
    <div>
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
                <label>Type:</label>
                <select name="type" class="form-control">
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
                    <input type="checkbox" class="form-check-input" name="foreign_key" v-model="column.is_foreign_key">
                    Foreign Key
                </label>
            </div>

            <div v-if="column.is_foreign_key">
                <div class="form-inline">
                    <div class="form-group">
                        <label>References:</label>
                        <input type="text" class="form-control" name="references" placeholder="MySQL Table name" v-model="column.foreign_key.references">
                    </div>

                    <div class="form-group">
                        <label>On Delete:</label>
                        <select name="on_delete" class="form-control" v-model="column.foreign_key.on_delete">
                            <option v-for="cascade in cascades" :value="cascade">{{ cascade }}</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>On Update:</label>
                        <select name="on_delete" class="form-control" v-model="column.foreign_key.on_update">
                            <option v-for="cascade in cascades" :value="cascade">{{ cascade }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <hr>
        </div>

        <div v-if="errors">
            <div class="alert alert-danger">
                {{ errors.message }}
            </div>
        </div>

        <button @click.prevent="addColumn" class="btn btn-default"><span class="fa fa-plus-circle"></span> Add Column</button>
        <button @click.prevent="sendColumns" class="btn btn-default" :disabled="columns.length === 0">Send Columns</button>

        <div v-if="code">
            <h2>Generated Code:</h2>
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
                mysql_types: ['integer', 'string', 'enum', 'boolean', 'timestamps'],
                code: null,
                errors: null
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
                        columns: this.columns
                    })
                    .then((response) => {
                        this.code = response.data.code;
                        this.errors = null;
                        // this.$nextTick(() => hljs.highlightBlock(this.$refs.code_ref))
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