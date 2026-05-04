/** 菜单树维护 */
(function (global) {
  const { createApp } = Vue;

  global.mountMenusAdmin = function (selector, opts) {
    const treeUrl = opts.treeUrl;
    const permissionsUrl = opts.permissionsUrl;

    const App = {
      template: `
        <div>
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;gap:12px;flex-wrap:wrap;">
            <div style="font-size:16px;font-weight:600;color:#303133;">菜单管理</div>
            <div style="display:flex;gap:8px;">
              <el-button v-if="has('menu:create')" type="primary" @click="openCreateRoot">新增根节点</el-button>
              <el-button @click="reload">刷新</el-button>
            </div>
          </div>

          <el-table :data="flatRows" border stripe v-loading="loading" row-key="id" default-expand-all :tree-props="{children:'children'}">
            <el-table-column prop="name" label="名称" min-width="200" />
            <el-table-column prop="icon" label="图标" width="120" />
            <el-table-column prop="path" label="地址" min-width="220" />
            <el-table-column prop="sort" label="排序" width="80" />
            <el-table-column label="类型" width="100">
              <template #default="{ row }">
                <span v-if="row.type===0">目录</span>
                <span v-else-if="row.type===1">菜单</span>
                <span v-else>按钮</span>
              </template>
            </el-table-column>
            <el-table-column label="状态" width="90">
              <template #default="{ row }">
                <el-tag :type="row.status===1?'success':'info'">{{ row.status===1?'启用':'禁用' }}</el-tag>
              </template>
            </el-table-column>
            <el-table-column prop="permission_code" label="权限标识" min-width="160" />
            <el-table-column fixed="right" label="操作" width="220">
              <template #default="{ row }">
                <el-button v-if="has('menu:create')" link type="primary" @click="openCreateChild(row)">新增子级</el-button>
                <el-button v-if="has('menu:edit')" link type="primary" @click="openEdit(row)">编辑</el-button>
                <el-button v-if="has('menu:delete')" link type="danger" @click="remove(row)">删除</el-button>
              </template>
            </el-table-column>
          </el-table>

          <el-dialog v-model="dlg.visible" :title="dlg.title" width="620px" destroy-on-close>
            <el-form label-width="110px">
              <el-form-item label="上级">
                <el-input :model-value="parentLabel" disabled />
              </el-form-item>
              <el-form-item label="名称">
                <el-input v-model="dlg.form.name" maxlength="128" show-word-limit />
              </el-form-item>
              <el-form-item label="图标">
                <el-input v-model="dlg.form.icon" placeholder="Element Plus 图标名，如 User" />
              </el-form-item>
              <el-form-item label="地址">
                <el-input v-model="dlg.form.path" placeholder="页面路径，如 /admin/content/users" />
              </el-form-item>
              <el-form-item label="排序">
                <el-input-number v-model="dlg.form.sort" :min="0" :max="999999" />
              </el-form-item>
              <el-form-item label="类型">
                <el-select v-model="dlg.form.type" style="width:100%">
                  <el-option :value="0" label="目录"/>
                  <el-option :value="1" label="菜单"/>
                  <el-option :value="2" label="按钮"/>
                </el-select>
              </el-form-item>
              <el-form-item label="状态">
                <el-select v-model="dlg.form.status" style="width:100%">
                  <el-option :value="1" label="启用"/>
                  <el-option :value="0" label="禁用"/>
                </el-select>
              </el-form-item>
              <el-form-item label="权限标识">
                <el-input v-model="dlg.form.permission_code" maxlength="128" />
              </el-form-item>
              <el-form-item label="权限字典">
                <el-select v-model="dlg.form.permission_id" clearable filterable placeholder="可选" style="width:100%">
                  <el-option v-for="p in permOptions" :key="p.id" :label="p.name + '（'+p.code+'）'" :value="p.id"/>
                </el-select>
              </el-form-item>
            </el-form>
            <template #footer>
              <el-button @click="dlg.visible=false">取消</el-button>
              <el-button type="primary" :loading="dlg.saving" @click="save">保存</el-button>
            </template>
          </el-dialog>
        </div>
      `,
      data() {
        return {
          loading: false,
          tree: [],
          flatRows: [],
          permOptions: [],
          dlg: {
            visible: false,
            title: '菜单',
            mode: 'create',
            saving: false,
            parent: null,
            form: {
              parent_id: 0,
              name: '',
              icon: '',
              path: '',
              sort: 0,
              status: 1,
              type: 1,
              permission_code: '',
              permission_id: null,
            },
          },
        };
      },
      computed: {
        parentLabel() {
          if (!this.dlg.parent) return '根（0）';
          return `${this.dlg.parent.name}（#${this.dlg.parent.id}）`;
        },
      },
      mounted() {
        this.loadPerms();
        this.reload();
      },
      methods: {
        has(code) {
          const list = global.__ADMIN_PERM_CODES__ || [];
          return list.includes(code);
        },
        async loadPerms() {
          try {
            const { data } = await axios.get(permissionsUrl);
            if (data.code === 0) this.permOptions = data.data || [];
          } catch (e) {
            /* ignore */
          }
        },
        normalize(rows) {
          return (rows || []).map((r) => ({
            ...r,
            children: r.children && r.children.length ? this.normalize(r.children) : [],
          }));
        },
        async reload() {
          this.loading = true;
          try {
            const { data } = await axios.get(treeUrl);
            if (data.code !== 0) throw new Error(data.message || '加载失败');
            this.tree = this.normalize(data.data || []);
            this.flatRows = this.tree;
          } catch (e) {
            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '加载失败');
          } finally {
            this.loading = false;
          }
        },
        blank(parentId) {
          return {
            parent_id: parentId,
            name: '',
            icon: '',
            path: '',
            sort: 0,
            status: 1,
            type: 1,
            permission_code: '',
            permission_id: null,
          };
        },
        openCreateRoot() {
          this.dlg.mode = 'create';
          this.dlg.title = '新增菜单（根）';
          this.dlg.parent = null;
          this.dlg.form = this.blank(0);
          this.dlg.visible = true;
        },
        openCreateChild(row) {
          this.dlg.mode = 'create';
          this.dlg.title = '新增子菜单';
          this.dlg.parent = row;
          this.dlg.form = this.blank(row.id);
          this.dlg.visible = true;
        },
        openEdit(row) {
          this.dlg.mode = 'edit';
          this.dlg.title = '编辑菜单';
          this.dlg.parent = row.parent_id ? { id: row.parent_id, name: '上级节点' } : null;
          this.dlg.form = {
            id: row.id,
            parent_id: row.parent_id,
            name: row.name,
            icon: row.icon || '',
            path: row.path || '',
            sort: row.sort,
            status: row.status,
            type: row.type,
            permission_code: row.permission_code || '',
            permission_id: row.permission_id,
          };
          this.dlg.visible = true;
        },
        async save() {
          this.dlg.saving = true;
          try {
            const body = { ...this.dlg.form };
            if (this.dlg.mode === 'create') {
              const { data } = await axios.post(treeUrl.replace(/\/tree$/, ''), body);
              if (data.code !== 0) throw new Error(data.message || '保存失败');
            } else {
              const id = body.id;
              const { data } = await axios.put(treeUrl.replace(/\/tree$/, '') + '/' + id, body);
              if (data.code !== 0) throw new Error(data.message || '保存失败');
            }
            ElementPlus.ElMessage.success('保存成功');
            this.dlg.visible = false;
            this.reload();
          } catch (e) {
            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '保存失败');
          } finally {
            this.dlg.saving = false;
          }
        },
        async remove(row) {
          try {
            await ElementPlus.ElMessageBox.confirm('确定删除该菜单？', '提示', { type: 'warning' });
          } catch (e) {
            return;
          }
          try {
            const { data } = await axios.delete(treeUrl.replace(/\/tree$/, '') + '/' + row.id);
            if (data.code !== 0) throw new Error(data.message || '删除失败');
            ElementPlus.ElMessage.success('已删除');
            this.reload();
          } catch (e) {
            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '删除失败');
          }
        },
      },
    };

    const zhCn = global.ElementPlusLocaleZhCn || {};
    const app = createApp(App);
    app.use(ElementPlus, { locale: zhCn });
    app.mount(selector);
  };
})(window);
