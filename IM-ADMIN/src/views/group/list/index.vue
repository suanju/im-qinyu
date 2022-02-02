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


    <el-dialog title="群成员" :visible.sync="visable">
        <el-table :data="fields" tooltip-effect="dark" style="width: 100%" border fit>
          <el-table-column prop="id" label="用户id" />
          <el-table-column label="用户名" prop="username" />
          <el-table-column prop="email" label="邮箱"/>
        </el-table>
      </el-dialog>

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
      tableFrom: 'table/group/group',
      departmentProps: {
        label: 'department_name'
      },
      visable: false,
      fields: null
    }
  },
  created() {
  },
  methods: {
    selectable(row) {
      return row.id !== 1
    },
    handleView(row) {
      this.visable = true
      this.$http.get('group/getGroupMembers/' + row.name).then(res => {
        this.fields = Object.values(res.data)
      })
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
