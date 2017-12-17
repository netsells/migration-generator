<template>
    <div class="form-inline">
        <div class="form-group">
            <label>References:</label>
            <input type="text" class="form-control" name="references" placeholder="MySQL Table name" v-model="table">
        </div>

        <div class="form-group">
            <label>On Delete:</label>
            <select name="on_delete" class="form-control">
                <option v-for="cascade in cascades" :value="on_delete">{{ cascade }}</option>
            </select>
        </div>

        <div class="form-group">
            <label>On Update:</label>
            <select name="on_delete" class="form-control">
                <option v-for="cascade in cascades" :value="on_update">{{ cascade }}</option>
            </select>
        </div>
    </div>
</template>

<script>
    export default {
        name: "foreign-key",
        data() {
            return {
                // this is more of a constant, as opposed to instance state
                cascades: ['cascade', 'restrict'],
                table: null,
                on_delete: 'restrict',
                on_update: 'restrict'
            }
        }
    }
</script>

<style scoped>

</style>