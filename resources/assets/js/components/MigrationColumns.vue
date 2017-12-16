<template>
    <div>
        <div class="form-group" v-for="(column, columnIndex) in columns">
            <button class="pull-right btn-sm btn-danger" @click.prevent="removeColumn(columnIndex)">Remove Column</button>

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
            </div>

            <hr>
        </div>

        <button @click.prevent="addColumn" class="btn btn-default">Add Column</button>
        <button @click.prevent="sendColumns" class="btn btn-default">Send Columns</button>
    </div>
</template>

<script>
    export default {
        name: "migration-columns",

        data() {
            return {
                columns: [],
                mysql_types: ['int', 'text', 'enum', 'boolean', 'datetime']
            };
        },

        methods: {
            addColumn() {
                let column = {
                    name: '',
                    type: 'text',
                    nullable: false
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
                        console.log(response.data)
                    })
                    .catch((error) => {
                        console.log(error)
                    })
            }
        }
    }
</script>

<style scoped>

</style>