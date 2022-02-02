<template>
  <div style="margin: 5px 5px;">
    <el-row :gutter="20">
      <el-col :span="19" style="padding-left: 0; width: 100%;">
        <catch-table style="width: 100%;"
          v-if="table"
          :ref="table.ref"
          :headers="table.headers"
          :border="true"
          :search="table.search"
          :filterParams="table.filterParams"
          :formCreate="formCreate"
          :actions="table.portrait"
          :table-events="table.events"
          :api-route="table.apiRoute"
          :selectable="selectable"
        />
      </el-col>
    </el-row>
  </div>
</template>
<script>
import renderTable from '@/views/render-table-form'
import status from './component/status'

export default {
  components: {
    status
  },
  mixins: [renderTable],

  data() {
    return {
      tableFrom: 'table/users/chat',
      departmentProps: {
        label: 'department_name'
      },
    }
  },
  created() {
  },
  methods: {
    selectable(row) {
      return row.id !== 1
    },

    beforeSubmit(row) {
      if (row.form.department_id instanceof Array) {
        row.form.department_id = row.form.department_id.length > 0 ? row.form.department_id.pop() : 0
      }
      return row
    },

    beforeDelete(row) {
      if (row.id === 1) {
        this.$message.error('超级管理员不允许删除')

        return false
      }
    },
    beforeCreate() {
      this.formCreate.fApi.updateValidate('password', [{ required: true, message: '密码必须填写'}], true)
      this.formCreate.fApi.refresh()
    },
    beforeUpdate() {
      this.formCreate.fApi.updateValidate('password', [{ required: false}])
    }
  }
}
</script>

<style>
  .custom-tree-node {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 14px;
    padding-right: 8px;
  }
  .department .el-tree {
    padding: 10px 10px;
    height: 360px;
  }
  .department .el-tree .el-tree-node__content {
      height: 35px;
  }

  .department .el-tree .el-tree-node__content .el-tree-node__label {
    font-size: 15px;
  }
</style>
