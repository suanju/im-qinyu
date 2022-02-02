<template>
  <div>
  <catch-table
    v-if="table"
    :ref="table.ref"
    :headers="table.headers"
    :border="true"
    :search="table.search"
    :table-events="table.events"
    :api-route="table.apiRoute"
  />
  <el-dialog title="表结构" :visible.sync="visable">
      <el-table :data="fields" tooltip-effect="dark" style="width: 100%" border fit>
        <el-table-column label="字段名称" prop="name" />
        <el-table-column prop="type" label="类型" width="150"/>
        <el-table-column prop="notnull" label="NULL" width="150">
          <template slot-scope="field">
            <el-tag type="primary">{{ field.row.notnull === true ? '是' : '否'}}</el-tag>
          </template>
        </el-table-column>
        <el-table-column prop="default" label="默认值" width="150"/>
        <el-table-column prop="comment" label="注释" />
      </el-table>
    </el-dialog>
  </div>
</template>

<script>
export default {
  data() {
    return {
      table: null,
      visable: false,
      fields: null
    }
  },
  created() {
    this.$http.get('table/system/database').then(response => {
      this.table = response.data.table;
    })
  },
  methods: {
    handleView(row) {
      this.visable = true
      this.$http.get('table/view/' + row.name).then(res => {
        this.fields = res.data
      })
    }
  }
}
</script>

<style scoped>

</style>
