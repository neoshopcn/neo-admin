/** 角色菜单授权弹窗 */
(function (global) {
  const { createApp } = Vue;

  global.mountRoleAssignBridge = function (opts) {
    const treeUrl = opts.treeUrl;
    const roleBaseUrl = opts.roleBaseUrl.replace(/\/$/, '');

    const Comp = {
      template: `
        <div>
          <el-dialog v-model="visible" title="菜单授权" width="520px" destroy-on-close @closed="onClosed">
            <div v-loading="loading">
              <el-tree
                ref="treeRef"
                :data="tree"
                show-checkbox
                node-key="id"
                :props="{ label: 'name', children: 'children' }"
                default-expand-all
                check-strictly
              />
            </div>
            <template #footer>
              <el-button @click="visible=false">取消</el-button>
              <el-button type="primary" :loading="saving" @click="save">保存</el-button>
            </template>
          </el-dialog>
        </div>
      `,
      data() {
        return {
          visible: false,
          loading: false,
          saving: false,
          tree: [],
          row: null,
        };
      },
      methods: {
        onClosed() {
          this.row = null;
        },
        normalizeTree(nodes) {
          return (nodes || []).map((n) => ({
            id: n.id,
            name: n.name + (n.type === 2 ? '（按钮）' : n.type === 0 ? '（目录）' : ''),
            children: n.children && n.children.length ? this.normalizeTree(n.children) : [],
          }));
        },
        async open(row) {
          this.row = row;
          this.visible = true;
          this.loading = true;
          try {
            const [tRes, rRes] = await Promise.all([
              axios.get(treeUrl),
              axios.get(`${roleBaseUrl}/${row.id}`),
            ]);
            if (tRes.data.code !== 0) throw new Error(tRes.data.message || '加载菜单失败');
            if (rRes.data.code !== 0) throw new Error(rRes.data.message || '加载角色失败');
            this.tree = this.normalizeTree(tRes.data.data || []);
            const keys = rRes.data.data.menu_ids || [];
            await this.$nextTick();
            const treeRef = this.$refs.treeRef;
            if (treeRef && typeof treeRef.setCheckedKeys === 'function') {
              treeRef.setCheckedKeys(keys, false);
            }
          } catch (e) {
            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '加载失败');
            this.visible = false;
          } finally {
            this.loading = false;
          }
        },
        async save() {
          if (!this.row) return;
          const treeRef = this.$refs.treeRef;
          const keys = treeRef && typeof treeRef.getCheckedKeys === 'function' ? treeRef.getCheckedKeys(false) : [];
          this.saving = true;
          try {
            const { data } = await axios.post(`${roleBaseUrl}/${this.row.id}/assign-menus`, { menu_ids: keys });
            if (data.code !== 0) throw new Error(data.message || '保存失败');
            ElementPlus.ElMessage.success('授权已保存');
            this.visible = false;
          } catch (e) {
            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '保存失败');
          } finally {
            this.saving = false;
          }
        },
      },
    };

    const zhCn = global.ElementPlusLocaleZhCn || {};
    const app = createApp(Comp);
    app.use(ElementPlus, { locale: zhCn });
    const vm = app.mount(opts.el);
    return {
      open: (row) => vm.open(row),
    };
  };
})(window);
