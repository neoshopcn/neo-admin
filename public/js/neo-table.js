/**
 * 通用表格：Vue3 + Element Plus CDN；需完整版 vue.global.js；中文包 ElementPlusLocaleZhCn
 */
(function (global) {
  const { createApp } = Vue;

  function ensureNeoTableStyles() {
    if (document.getElementById('neo-table-styles')) {
      return;
    }
    const el = document.createElement('style');
    el.id = 'neo-table-styles';
    el.textContent = `
      .neo-table-shell {
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e8ecf2;
        box-shadow: 0 6px 22px rgba(31, 45, 61, 0.07);
        background: #fff;
      }
      .neo-table-shell .neo-table-inner .el-table {
        --el-table-border-color: transparent;
      }
      .neo-table-shell .neo-table-inner .el-table::before,
      .neo-table-shell .neo-table-inner .el-table::after { display: none; }
      .neo-table-shell .neo-table-inner .el-table__header-wrapper {
        background: #fff !important;
      }
      .neo-table-shell .neo-table-inner .el-table__header-wrapper th.el-table__cell {
        background: #fff !important;
        color: #606266;
        font-weight: 600;
        font-size: 13px;
        border-bottom: 1px solid #eef1f6 !important;
      }
      .neo-table-shell .neo-table-inner .el-table__body-wrapper .el-table__row td.el-table__cell {
        background: #ffffff;
        border-bottom: 1px solid #f0f2f5 !important;
      }
      .neo-table-shell .neo-table-inner .el-table__body-wrapper .el-table__row:nth-child(even) td.el-table__cell {
        background: #f9fafc;
      }
      .neo-table-shell .neo-table-inner .el-table__body-wrapper .el-table__row:hover td.el-table__cell {
        background: #f3f7ff !important;
      }
      .neo-table-footer {
        padding: 12px 16px 14px;
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 12px;
        border-top: 1px solid #eef1f6;
        background: #fff;
      }
      .neo-table-footer__selection {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        min-width: 0;
      }
      /* 与左侧批量操作同一行时靠右；仅分页时也靠右 */
      .neo-table-footer__pager {
        display: flex;
        justify-content: flex-end;
        flex-shrink: 0;
        margin-left: auto;
      }
      /* 筛选区与表头同为白底，不在此处再画横线，避免与表头灰带一起形成「割裂」感 */
      .neo-table-shell > .neo-table-toolbar {
        padding: 14px 16px 12px;
        background: #fff;
      }
      .neo-table-toolbar .neo-table-toolbar-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        row-gap: 8px;
      }
      /* 用 margin 代替 column-gap，避免部分 EP 控件溢出/负边距把水平间隙「吃掉」 */
      .neo-table-toolbar .neo-table-toolbar-row > *:not(:last-child) {
        margin-inline-end: 8px;
      }
      .neo-table-toolbar .neo-table-toolbar-row > * {
        flex: 0 0 auto;
        min-width: 0;
      }
      /* 固定宽度 + 裁切横向溢出，避免日期框绘制超出槽位 */
      .neo-table-toolbar-daterange {
        width: 260px;
        max-width: 100%;
        box-sizing: border-box;
        overflow-x: clip;
        overflow-y: visible;
      }
      .neo-table-toolbar-daterange .el-date-editor.el-date-editor--daterange {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box;
      }
      /* 工具栏内按钮：抵消 EP 相邻 margin，仅作用于 neo-table-shell 顶部筛选区 */
      .neo-table-toolbar .el-button + .el-button {
        margin-left: 0;
      }
      .neo-table-shell .neo-table-actions-col .cell {
        white-space: nowrap;
        overflow: visible;
      }
      .neo-table-actions {
        display: inline-flex;
        flex-wrap: nowrap;
        align-items: center;
        white-space: nowrap;
        vertical-align: middle;
      }
      .neo-table-actions .el-button.is-link {
        flex-shrink: 0;
      }
    `;
    document.head.appendChild(el);
  }

  function hasPerm(code) {
    if (!code) return true;
    const list = global.__ADMIN_PERM_CODES__ || [];
    return list.includes(code);
  }

  function cellVal(row, prop) {
    if (!prop) return '';
    return prop.split('.').reduce((o, k) => (o == null ? o : o[k]), row);
  }

  function extraVisible(ex, row) {
    if (!ex.when) return true;
    const w = ex.when;
    const v = cellVal(row, w.field);
    if (w.equals !== undefined) return v == w.equals;
    if (w.truthy !== undefined) return !!v === w.truthy;
    return true;
  }

  global.mountNeoTable = function (selector, cfg, hooks) {
    hooks = hooks || {};
    ensureNeoTableStyles();

    const zhCn = global.ElementPlusLocaleZhCn || {};

    const NeoPage = {
      props: { cfg: Object, hooks: { type: Object, default: () => ({}) } },
      data() {
        const filters = {};
        (cfg.filters || []).forEach((f) => {
          if (f.type === 'daterange') {
            filters[f.startKey] = '';
            filters[f.endKey] = '';
            filters['__dr_' + f.startKey] = '';
          } else {
            filters[f.key] = f.default != null ? f.default : '';
          }
        });
        return {
          loading: false,
          rows: [],
          pagination: { page: 1, page_size: 15, total: 0 },
          filters,
          dialogVisible: false,
          dialogMode: 'create',
          dialogLoading: false,
          form: {},
          detailVisible: false,
          detailRow: null,
          selectOptions: {},
          selectedRows: [],
        };
      },
      mounted() {
        this.bootstrapSelects();
        this.loadList();
      },
      methods: {
        hasPerm,
        /** 嵌套字段取值，须挂在 methods */
        cellVal,
        avatarFallback(row) {
          const n = (row && (row.name || row.username)) ? String(row.name || row.username) : '';
          return n ? n.charAt(0).toUpperCase() : '?';
        },
        async bootstrapSelects() {
          const fields = this.cfg.formFields || [];
          for (const f of fields) {
            if (f.input === 'select' && f.optionsUrl && f.prop) {
              try {
                const { data } = await axios.get(f.optionsUrl);
                if (data.code === 0) {
                  this.selectOptions[f.prop] = (data.data || []).map((r) => ({
                    label: r.name || r.code || String(r.id),
                    value: r.id,
                  }));
                }
              } catch (e) {
                /* ignore */
              }
            }
          }
        },
        buildQuery() {
          const q = { page: this.pagination.page, page_size: this.pagination.page_size };
          Object.keys(this.filters).forEach((k) => {
            if (k.startsWith('__dr_')) return;
            q[k] = this.filters[k];
          });
          return q;
        },
        async loadList() {
          this.loading = true;
          try {
            const { data } = await axios.get(this.cfg.listUrl, { params: this.buildQuery() });
            if (data.code !== 0) throw new Error(data.message || '加载失败');
            this.rows = data.data.list;
            this.pagination.total = data.data.pagination.total;
            this.pagination.page = data.data.pagination.page;
            this.pagination.page_size = data.data.pagination.page_size;
            if (!this.cfg.selectionReserve) {
              this.$nextTick(() => {
                try {
                  this.$refs.neoTableRef?.clearSelection?.();
                } catch (e2) { /* ignore */ }
                this.selectedRows = [];
              });
            }
          } catch (e) {
            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '加载失败');
          } finally {
            this.loading = false;
          }
        },
        onSearch() {
          this.pagination.page = 1;
          this.loadList();
        },
        onPageChange(p) {
          this.pagination.page = p;
          this.loadList();
        },
        onSizeChange(s) {
          this.pagination.page_size = s;
          this.pagination.page = 1;
          this.loadList();
        },
        blankForm() {
          const o = {};
          (this.cfg.formFields || []).forEach((f) => {
            if (this.dialogMode === 'create' && f.editOnly) return;
            if (this.dialogMode === 'edit' && f.createOnly) return;
            if (f.input === 'select') {
              if (f.multiple) {
                o[f.prop] = [];
              } else {
                const opts = f.options || this.selectOptions[f.prop] || [];
                o[f.prop] = opts.length ? opts[0].value : null;
              }
            } else {
              o[f.prop] = '';
            }
          });
          return o;
        },
        openCreate() {
          if (!hasPerm(this.cfg.perms?.create)) return;
          this.dialogMode = 'create';
          this.form = this.blankForm();
          this.dialogVisible = true;
        },
        async openEdit(row) {
          if (!hasPerm(this.cfg.perms?.edit)) return;
          this.dialogMode = 'edit';
          this.dialogLoading = true;
          this.dialogVisible = true;
          try {
            const id = row[this.cfg.rowKey || 'id'];
            const url = this.cfg.detailUrl || `${this.cfg.listUrl.replace(/\/$/, '')}/${id}`;
            const { data } = await axios.get(url);
            if (data.code !== 0) throw new Error(data.message || '加载失败');
            const src = data.data;
            const form = {};
            (this.cfg.formFields || []).forEach((f) => {
              if (f.createOnly) return;
              let v = cellVal(src, f.prop);
              if (f.prop === 'role_ids' && Array.isArray(src.roles)) {
                v = src.roles.map((r) => r.id);
              }
              if (f.prop === 'tags' && Array.isArray(v)) {
                v = v.join(',');
              }
              if (f.prop === 'password') v = '';
              const selectEmpty = f.input === 'select' ? (f.multiple ? [] : null) : '';
              form[f.prop] = v ?? selectEmpty;
            });
            form.id = src.id;
            this.form = form;
          } catch (e) {
            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '加载失败');
            this.dialogVisible = false;
          } finally {
            this.dialogLoading = false;
          }
        },
        openView(row) {
          if (!hasPerm(this.cfg.perms?.view)) return;
          this.detailRow = row;
          this.detailVisible = true;
        },
        async remove(row) {
          if (!hasPerm(this.cfg.perms?.delete)) return;
          try {
            await ElementPlus.ElMessageBox.confirm('确定删除该记录？', '提示', { type: 'warning' });
          } catch (e) {
            return;
          }
          try {
            const id = row[this.cfg.rowKey || 'id'];
            await axios.delete(`${this.cfg.listUrl.replace(/\/$/, '')}/${id}`);
            ElementPlus.ElMessage.success('已删除');
            this.loadList();
          } catch (e) {
            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '删除失败');
          }
        },
        uploadSceneForField(f) {
          if (f.uploadSceneFromForm && this.form[f.uploadSceneFromForm]) {
            return String(this.form[f.uploadSceneFromForm]);
          }
          return f.uploadScene || 'avatar';
        },
        uploadTagsForField(f) {
          if (!f.uploadTagsFromForm) return '';
          const v = this.form[f.uploadTagsFromForm];
          return v != null ? String(v) : '';
        },
        async save() {
          if (this.dialogMode === 'create' && this.hooks && this.hooks.skipCreateApi === true) {
            this.dialogLoading = true;
            try {
              const pathField = this.hooks.createPathField || 'storage_path';
              if (!String(this.form[pathField] || '').trim()) {
                ElementPlus.ElMessage.warning(this.hooks.createPathHint || '请先完成必填项');
                return;
              }
              ElementPlus.ElMessage.success('已入库');
              this.dialogVisible = false;
              this.loadList();
            } finally {
              this.dialogLoading = false;
            }
            return;
          }
          this.dialogLoading = true;
          try {
            const body = { ...this.form };
            if (this.dialogMode === 'edit' && !body.password) delete body.password;
            if (this.dialogMode === 'create') {
              const { data } = await axios.post(this.cfg.listUrl.replace(/\/$/, ''), body);
              if (data.code !== 0) throw new Error(data.message || '保存失败');
            } else {
              const id = body.id;
              const { data } = await axios.put(`${this.cfg.listUrl.replace(/\/$/, '')}/${id}`, body);
              if (data.code !== 0) throw new Error(data.message || '保存失败');
            }
            ElementPlus.ElMessage.success('保存成功');
            this.dialogVisible = false;
            this.loadList();
          } catch (e) {
            ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '保存失败');
          } finally {
            this.dialogLoading = false;
          }
        },
        async handleExtra(action, row) {
          if (this.hooks && typeof this.hooks.onExtra === 'function') {
            await this.hooks.onExtra(action, row, this);
            return;
          }
          if (action === 'resetPwd') {
            try {
              await ElementPlus.ElMessageBox.confirm('确定为该用户重置随机密码？', '提示', { type: 'warning' });
            } catch (e) {
              return;
            }
            try {
              const id = row[this.cfg.rowKey || 'id'];
              const { data } = await axios.post(`${this.cfg.listUrl.replace(/\/$/, '')}/${id}/reset-password`, {});
              if (data.code !== 0) throw new Error(data.message || '失败');
              const pwd = data.data?.password_plain || '';
              await ElementPlus.ElMessageBox.alert(`新密码：${pwd}`, '重置成功', { type: 'success' });
            } catch (e) {
              ElementPlus.ElMessage.error(e.response?.data?.message || e.message || '重置失败');
            }
          }
        },
        renderCell(row, col) {
          const v = cellVal(row, col.prop);
          if (col.tag && col.statusLabels && col.statusLabels.length >= 2) {
            const on = Number(v) === 1;
            return on ? col.statusLabels[0] : col.statusLabels[1];
          }
          if (col.tag && col.prop === 'status') {
            const on = Number(v) === 1;
            if (col.statusLabels && col.statusLabels.length >= 2) {
              return on ? col.statusLabels[0] : col.statusLabels[1];
            }
            return on ? '启用' : '禁用';
          }
          return v == null ? '' : v;
        },
        tagType(col, row) {
          const v = cellVal(row, col.prop);
          if (col.tag && col.statusLabels) return Number(v) === 1 ? 'success' : 'info';
          if (col.prop === 'status') return Number(v) === 1 ? 'success' : 'info';
          return '';
        },
        extraVisible(ex, row) {
          return extraVisible(ex, row);
        },
        fieldDisabled(f) {
          return this.dialogMode === 'edit' && f.disableOnEdit;
        },
        fieldVisible(f) {
          if (this.dialogMode === 'create' && f.editOnly) return false;
          if (this.dialogMode === 'edit' && f.createOnly) return false;
          return true;
        },
        selectOpts(f) {
          return f.options || this.selectOptions[f.prop] || [];
        },
        onRangeChange(f, val) {
          this.filters[f.startKey] = val && val[0] ? val[0] : '';
          this.filters[f.endKey] = val && val[1] ? val[1] : '';
        },
        onSelectionChange(rows) {
          this.selectedRows = rows || [];
        },
        togglePageSelectAll(checked) {
          const t = this.$refs.neoTableRef;
          if (!t) return;
          this.rows.forEach((row) => {
            t.toggleRowSelection(row, !!checked);
          });
        },
        clearSelection() {
          const t = this.$refs.neoTableRef;
          if (t) t.clearSelection();
          this.selectedRows = [];
        },
        async runBatch(key) {
          if (!this.selectedRows.length) return;
          if (this.hooks && typeof this.hooks.onBatchAction === 'function') {
            await this.hooks.onBatchAction(key, [...this.selectedRows], this);
            return;
          }
          ElementPlus.ElMessage.info('请在 hooks.onBatchAction 中处理：' + key);
        },
        runRowQuick(key, row) {
          if (this.hooks && typeof this.hooks.onRowQuick === 'function') {
            this.hooks.onRowQuick(key, row, this);
            return;
          }
          ElementPlus.ElMessage.info('请在 hooks.onRowQuick 中处理：' + key);
        },
        onInlineNumberChange(col, row) {
          const val = row[col.prop];
          if (this.hooks && typeof this.hooks.onInlineNumberChange === 'function') {
            this.hooks.onInlineNumberChange(col.prop, row, val, this);
          }
        },
        async onInlineSelectCommand(col, row, cmd) {
          if (this.hooks && typeof this.hooks.onInlineSelectChange === 'function') {
            await this.hooks.onInlineSelectChange(col.prop, row, cmd, this);
            return;
          }
          row[col.prop] = cmd;
        },
      },
      template: `
        <div>
          <div class="neo-table-shell">
            <div class="neo-table-toolbar">
              <div class="neo-table-toolbar-row">
                <template v-for="f in (cfg.filters||[])" :key="f.key || (f.startKey+'_range')">
                  <el-input v-if="f.type==='input'" v-model="filters[f.key]" clearable :placeholder="f.placeholder" style="width:200px" @keyup.enter="onSearch"/>
                  <el-select v-else-if="f.type==='select'" v-model="filters[f.key]" clearable :placeholder="f.placeholder" style="width:140px">
                    <el-option v-for="o in f.options" :key="String(o.value)" :label="o.label" :value="o.value"/>
                  </el-select>
                  <span v-else-if="f.type==='daterange'" class="neo-table-toolbar-daterange">
                    <el-date-picker v-model="filters['__dr_'+f.startKey]" type="daterange"
                      range-separator="至" start-placeholder="开始" end-placeholder="结束" value-format="YYYY-MM-DD"
                      style="width:100%" @change="onRangeChange(f, $event)"/>
                  </span>
                </template>
                <el-button type="primary" @click="onSearch">查询</el-button>
                <el-button v-if="cfg.perms && cfg.perms.create && hasPerm(cfg.perms.create)" type="success" @click="openCreate">新增</el-button>
              </div>
            </div>

            <div class="neo-table-inner">
              <el-table
                ref="neoTableRef"
                :data="rows"
                border
                v-loading="loading"
                style="width:100%"
                :row-key="cfg.rowKey || 'id'"
                @selection-change="onSelectionChange">
                <el-table-column v-if="cfg.selectionEnabled" type="selection" width="48" :reserve-selection="!!cfg.selectionReserve"/>
                <el-table-column v-for="col in cfg.columns" :key="col.prop"
                  :prop="col.prop" :label="col.label" :width="col.width" :min-width="col.minWidth">
                  <template #default="{ row }">
                    <el-dropdown v-if="col.inlineSelect" trigger="click" @command="(cmd) => onInlineSelectCommand(col, row, cmd)">
                      <span style="cursor:pointer;display:inline-flex;align-items:center;gap:6px;" class="neo-inline-select-hit">
                        <el-tag v-if="col.tag" :type="tagType(col, row)">{{ renderCell(row, col) }}</el-tag>
                        <span v-else style="font-size:13px;color:#303133;">{{ renderCell(row, col) }}</span>
                        <el-icon style="font-size:14px;color:var(--el-color-primary);"><component :is="col.inlineSelectIcon || 'EditPen'" /></el-icon>
                      </span>
                      <template #dropdown>
                        <el-dropdown-menu>
                          <el-dropdown-item v-for="opt in (col.inlineSelectOptions||[])" :key="'sel_'+String(opt.value)" :command="opt.value">{{ opt.label }}</el-dropdown-item>
                        </el-dropdown-menu>
                      </template>
                    </el-dropdown>
                    <el-input-number
                      v-else-if="col.inlineNumber"
                      v-model="row[col.prop]"
                      :min="col.inlineMin != null ? col.inlineMin : 0"
                      :max="col.inlineMax != null ? col.inlineMax : 999999"
                      :step="col.inlineStep != null ? col.inlineStep : 1"
                      :precision="col.inlinePrecision != null ? col.inlinePrecision : 0"
                      size="small"
                      controls-position="right"
                      style="width:118px"
                      @change="onInlineNumberChange(col, row)"
                    />
                    <el-avatar v-else-if="col.avatar" :size="36" :src="cellVal(row, col.prop)">
                      <span style="font-size:13px">{{ avatarFallback(row) }}</span>
                    </el-avatar>
                    <el-tag v-else-if="col.tag" :type="tagType(col, row)">{{ renderCell(row, col) }}</el-tag>
                    <span v-else>{{ renderCell(row, col) }}</span>
                  </template>
                </el-table-column>

                <el-table-column v-if="(cfg.rowQuickActions||[]).length" fixed="right" label="快捷" :width="cfg.rowQuickWidth || 140">
                  <template #default="{ row }">
                    <template v-for="qa in cfg.rowQuickActions" :key="qa.key">
                      <el-button v-if="!qa.perm || hasPerm(qa.perm)" link type="primary" size="small" @click="runRowQuick(qa.key, row)">{{ qa.label }}</el-button>
                    </template>
                  </template>
                </el-table-column>

                <el-table-column v-if="!cfg.hideActions" fixed="right" label="操作" :min-width="cfg.actionsMinWidth || 420" class-name="neo-table-actions-col">
                  <template #default="{ row }">
                    <span class="neo-table-actions">
                      <el-button v-if="cfg.perms?.view && hasPerm(cfg.perms.view)" link type="primary" @click="openView(row)">查看</el-button>
                      <el-button v-if="cfg.perms?.edit && hasPerm(cfg.perms.edit)" link type="primary" @click="openEdit(row)">编辑</el-button>
                      <el-button v-if="cfg.perms?.delete && hasPerm(cfg.perms.delete)" link type="danger" @click="remove(row)">删除</el-button>
                      <template v-for="ex in (cfg.perms?.extra||[])" :key="ex.key">
                        <el-button v-if="hasPerm(ex.perm) && extraVisible(ex, row)" link :type="ex.btnType || 'warning'" @click="handleExtra(ex.key, row)">{{ ex.label }}</el-button>
                      </template>
                    </span>
                  </template>
                </el-table-column>
              </el-table>
            </div>

            <div class="neo-table-footer">
              <div v-if="cfg.selectionEnabled" class="neo-table-footer__selection">
                <span style="font-size:13px;color:#606266;">已选 <strong>{{ selectedRows.length }}</strong> 条</span>
                <template v-for="ba in (cfg.batchActions||[])" :key="ba.key">
                  <el-button v-if="!ba.perm || hasPerm(ba.perm)" size="small" :type="ba.btnType || 'primary'" :plain="ba.btnPlain !== false" :disabled="selectedRows.length===0" @click="runBatch(ba.key)">{{ ba.label }}</el-button>
                </template>
              </div>
              <div class="neo-table-footer__pager">
                <el-pagination background layout="total, sizes, prev, pager, next"
                  :total="pagination.total"
                  v-model:page-size="pagination.page_size"
                  v-model:current-page="pagination.page"
                  :page-sizes="[10,15,20,50,100]"
                  @current-change="onPageChange"
                  @size-change="onSizeChange"/>
              </div>
            </div>
          </div>

          <el-dialog v-model="dialogVisible" :title="dialogMode==='create'?'新增':'编辑'" width="560px" destroy-on-close>
            <div v-loading="dialogLoading">
              <el-form label-width="96px">
                <template v-for="f in (cfg.formFields||[])" :key="f.prop+'_'+(f.editOnly?'e':'c')">
                  <el-form-item v-if="fieldVisible(f)" :label="f.label">
                    <el-input v-if="!f.input || f.input==='text'" v-model="form[f.prop]" :disabled="fieldDisabled(f)" :placeholder="f.placeholder || ''"/>
                    <el-input v-else-if="f.input==='password'" v-model="form[f.prop]" type="password" show-password autocomplete="new-password"/>
                    <el-input v-else-if="f.input==='email'" v-model="form[f.prop]" type="email"/>
                    <el-select v-else-if="f.input==='select'" v-model="form[f.prop]" filterable style="width:100%"
                      :multiple="!!f.multiple" :collapse-tags="!!f.multiple" collapse-tags-tooltip>
                      <el-option v-for="o in selectOpts(f)" :key="String(o.value)" :label="o.label" :value="o.value"/>
                    </el-select>
                    <div v-else-if="f.input==='upload'" style="width:100%">
                      <NeoUploadField
                        v-if="cfg.uploadUrl"
                        :model-value="form[f.prop]"
                        @update:model-value="(v) => { form[f.prop] = v; }"
                        :action="cfg.uploadUrl"
                        :scene="uploadSceneForField(f)"
                        :accept="f.uploadAccept || 'image/jpeg,image/png,image/gif,image/webp'"
                        :tip="f.placeholder || ''"
                        :storage-base="cfg.storagePublicBase || ''"
                        :pick-from-library="f.pickFromLibrary !== false"
                        :resource-list-url="cfg.resourcesListUrl || '/admin/api/resources'"
                        :tags="uploadTagsForField(f)"
                      />
                      <div v-else style="color:#f56c6c;font-size:13px;">未配置 uploadUrl，无法使用上传控件</div>
                      <el-input v-model="form[f.prop]" clearable style="margin-top:10px" placeholder="或直接粘贴图片 URL"/>
                    </div>
                  </el-form-item>
                </template>
              </el-form>
            </div>
            <template #footer>
              <el-button @click="dialogVisible=false">取消</el-button>
              <el-button type="primary" :loading="dialogLoading" @click="save">保存</el-button>
            </template>
          </el-dialog>

          <el-dialog v-model="detailVisible" title="查看" width="640px">
            <el-descriptions :column="1" border>
              <el-descriptions-item v-for="col in cfg.columns" :key="col.prop" :label="col.label">
                <el-avatar v-if="col.avatar" :size="40" :src="cellVal(detailRow, col.prop)">
                  <span style="font-size:14px">{{ avatarFallback(detailRow) }}</span>
                </el-avatar>
                <span v-else-if="col.tag">
                  <el-tag :type="tagType(col, detailRow)">{{ renderCell(detailRow, col) }}</el-tag>
                </span>
                <span v-else>{{ renderCell(detailRow, col) }}</span>
              </el-descriptions-item>
            </el-descriptions>
          </el-dialog>
        </div>
      `,
    };

    const Root = {
      components: { NeoPage },
      template: `<NeoPage :cfg="cfg" :hooks="hooks"/>`,
      data() {
        return { cfg, hooks };
      },
    };

    const app = createApp(Root);
    app.use(ElementPlus, { locale: zhCn });
    if (global.NeoUploadField) {
      app.component('NeoUploadField', global.NeoUploadField);
    }
    const neoIcons = global.ElementPlusIconsVue || {};
    for (const [key, comp] of Object.entries(neoIcons)) {
      app.component(key, comp);
    }
    app.mount(selector);
  };
})(window);
