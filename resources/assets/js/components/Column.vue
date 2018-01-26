<template>
    <div class="form-group">
        <button class="pull-right btn-sm btn-danger" @click.prevent="removeColumn">
            <span class="fa fa-times"></span> Remove Column
        </button>

        <div class="form-group" v-if="canHaveName">
            <label>Column name:</label>
            <input type="text" class="form-control" name="column_name" v-model="column.name">
        </div>

        <div class="form-group" v-if="canHaveDefaultValue">
            <label>Default Value:</label>
            <template v-if="allowedValues instanceof Array">
                <select class="form-control" v-model="column.default">
                    <option v-for="(option, index) in allowedValues" :value="option.value">{{ option.name }}</option>
                </select>
            </template>
            <input v-else type="text" class="form-control" v-model="column.default">
        </div>

        <div class="form-group">
            <label>Type:</label>
            <select name="type" class="form-control" v-model="column.type">
                <option v-for="type in types" :value="type">{{ type }}</option>
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
</template>

<script>
    import columns from '../modules/ColumnTypes';

    export default {
        name: 'column',
        props: ['column'],

        data() {
            return {
                types: columns,
            };
        },

        computed: {
            canHaveName() {
                return this.column.type !== 'timestamps';
            },

            canHaveDefaultValue() {
                return this.column.type !== 'timestamps';
            },

            allowedValues() {
                if (this.column.type === 'boolean') {
                    return [
                        {
                            name: 'True',
                            value: true,
                        },
                        {
                            name: 'False',
                            value: false,
                        }
                    ];
                }

                return false;
            },
        },

        methods: {
            removeColumn() {
                console.log(this.key);
            }
        },

        watch: {
            'column.default': () => {
                console.log(this);
                if (this.column.type === 'enum') {

                }
            },

            'column.type': () => {
                console.log(this);
            },


        }
    }

</script>