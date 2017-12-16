<template>
    <div>
        <div class="form-group" v-for="column in columns">
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
            }
        }
    }
</script>

<style scoped>

</style>