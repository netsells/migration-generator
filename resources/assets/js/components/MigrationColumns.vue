<template>
    <div>
        <div class="form-group" v-for="(column, columnIndex) in columns">
            <button class="pull-right btn-sm btn-danger" @click.prevent="removeColumn(columnIndex)">
                Remove Column
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
            </div>

            <hr>
        </div>

        <div v-if="errors">
            <div class="alert alert-danger">
                {{ errors.message }}
            </div>
        </div>

        <button @click.prevent="addColumn" class="btn btn-default">Add Column</button>
        <button @click.prevent="sendColumns" class="btn btn-default">Send Columns</button>

        <div v-if="code">
            <h2>Generated Code:</h2>
            <div class="form-group">
                <textarea class="form-control">{{ code }}</textarea>
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
                mysql_types: ['integer', 'string', 'enum', 'boolean', 'timestamps'],
                code: null,
                errors: null
            };
        },

        methods: {
            addColumn() {
                let column = {
                    name: '',
                    type: 'text',
                    nullable: false,
                    unsigned: false
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