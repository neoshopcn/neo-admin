/** 动态配置中心：分组 Tab + 分区 + 动态表单项 */
(function (global) {
  const { createApp } = Vue;

  global.mountConfigCenter = function (selector, cfg) {
    const permCodes = global.__ADMIN_PERM_CODES__ || [];

    const App = {
      template: `
        <div class="neo-config-card" v-loading="loading">
          <div class="neo-config-top">
            <div class="neo-config-header">
              <div class="neo-config-title">{{ title }}</div>
              <el-button @click="reload">刷新</el-button>
            </div>
          </div>

          <el-empty v-if="!loading && groups.length===0" description="暂无配置分组" style="padding:40px 24px;background:#fff;" />

          <el-tabs
            v-else
            v-model="activeGroup"
            class="neo-config-tabs"
            :before-leave="beforeGroupLeave"
            @tab-change="onGroupChange"
          >
            <el-tab-pane
              v-for="g in groups"
              :key="g.name"
              :name="g.name"
              :label="groupTabLabel(g)"
            >
              <el-empty v-if="!g.sections || g.sections.length===0" description="该分组下暂无分区" />

              <template v-else>
                <template v-if="g.sections.length === 1">
                  <el-form
                    label-width="160px"
                    style="max-width:760px;margin-top:12px;"
                  >
                    <template v-for="item in g.sections[0].items" :key="item.id">
                      <el-form-item
                        :label="item.label"
                        :required="item.required===1"
                      >
                        <el-input
                          v-if="item.type==='text'"
                          v-model="formValues[item.id]"
                          :placeholder="'请输入' + item.label"
                          clearable
                        />
                        <el-input
                          v-else-if="item.type==='password'"
                          v-model="formValues[item.id]"
                          type="password"
                          show-password
                          :placeholder="'请输入' + item.label"
                          clearable
                        />
                        <el-input-number
                          v-else-if="item.type==='number'"
                          v-model="formValues[item.id]"
                          controls-position="right"
                          style="width:100%;"
                        />
                        <el-switch
                          v-else-if="item.type==='switch'"
                          v-model="formValues[item.id]"
                          :active-value="1"
                          :inactive-value="0"
                        />
                        <el-select
                          v-else-if="item.type==='select'"
                          v-model="formValues[item.id]"
                          clearable
                          placeholder="请选择"
                          style="width:100%;"
                        >
                          <el-option
                            v-for="opt in normalizeOptions(item.options)"
                            :key="opt.value"
                            :label="opt.label"
                            :value="opt.value"
                          />
                        </el-select>
                        <el-input
                          v-else-if="item.type==='textarea'"
                          v-model="formValues[item.id]"
                          type="textarea"
                          :rows="8"
                          :placeholder="'请输入' + item.label"
                        />
                        <el-input
                          v-else-if="item.type==='json'"
                          v-model="formValues[item.id]"
                          type="textarea"
                          :rows="6"
                          placeholder="JSON 格式"
                        />
                        <el-input
                          v-else
                          v-model="formValues[item.id]"
                          :placeholder="'请输入' + item.label"
                          clearable
                        />
                      </el-form-item>
                    </template>
                    <el-empty v-if="!g.sections[0].items || g.sections[0].items.length===0" description="暂无配置项" />
                  </el-form>
                </template>

                <el-tabs v-else v-model="activeSection[g.name]" type="card" class="neo-config-section-tabs">
                  <el-tab-pane
                    v-for="s in g.sections"
                    :key="s.name"
                    :name="s.name"
                    :label="s.label"
                  >
                    <el-form
                      :ref="'form_' + g.name + '_' + s.name"
                      label-width="160px"
                      style="max-width:760px;"
                    >
                      <el-form-item
                        v-for="item in s.items"
                        :key="item.id"
                        :label="item.label"
                        :required="item.required===1"
                      >
                        <el-input
                          v-if="item.type==='text'"
                          v-model="formValues[item.id]"
                          :placeholder="'请输入' + item.label"
                          clearable
                        />
                        <el-input
                          v-else-if="item.type==='password'"
                          v-model="formValues[item.id]"
                          type="password"
                          show-password
                          :placeholder="'请输入' + item.label"
                          clearable
                        />
                        <el-input-number
                          v-else-if="item.type==='number'"
                          v-model="formValues[item.id]"
                          controls-position="right"
                          style="width:100%;"
                        />
                        <el-switch
                          v-else-if="item.type==='switch'"
                          v-model="formValues[item.id]"
                          :active-value="1"
                          :inactive-value="0"
                        />
                        <el-select
                          v-else-if="item.type==='select'"
                          v-model="formValues[item.id]"
                          clearable
                          placeholder="请选择"
                          style="width:100%;"
                        >
                          <el-option
                            v-for="opt in normalizeOptions(item.options)"
                            :key="opt.value"
                            :label="opt.label"
                            :value="opt.value"
                          />
                        </el-select>
                        <el-input
                          v-else-if="item.type==='textarea'"
                          v-model="formValues[item.id]"
                          type="textarea"
                          :rows="8"
                          :placeholder="'请输入' + item.label"
                        />
                        <el-input
                          v-else-if="item.type==='json'"
                          v-model="formValues[item.id]"
                          type="textarea"
                          :rows="6"
                          placeholder="JSON 格式"
                        />
                        <el-input
                          v-else
                          v-model="formValues[item.id]"
                          :placeholder="'请输入' + item.label"
                          clearable
                        />
                      </el-form-item>

                      <el-empty v-if="!s.items || s.items.length===0" description="该分区下暂无配置项" />
                    </el-form>
                  </el-tab-pane>
                </el-tabs>
              </template>

              <div
                v-if="canEdit && g.sections && g.sections.length"
                class="neo-config-save"
              >
                <el-button type="primary" :loading="savingGroup === g.name" @click="saveGroup(g)">保存当前配置</el-button>
              </div>
            </el-tab-pane>
          </el-tabs>
        </div>
      `,
      data() {
        return {
          title: cfg.title || '配置中心',
          loadUrl: cfg.loadUrl,
          saveUrl: cfg.saveUrl,
          editPerm: (cfg.perms && cfg.perms.edit) || '',
          loading: false,
          savingGroup: '',
          groups: [],
          activeGroup: '',
          activeSection: {},
          formValues: {},
          originalValues: {},
        };
      },
      computed: {
        canEdit() {
          return !this.editPerm || permCodes.includes(this.editPerm);
        },
      },
      mounted() {
        this.reload();
      },
      methods: {
        has(code) {
          return permCodes.includes(code);
        },
        normalizeOptions(options) {
          if (!options) return [];
          if (Array.isArray(options)) {
            return options.map(function (o) {
              if (typeof o === 'string') return { label: o, value: o };
              return { label: o.label || o.value, value: o.value };
            });
          }
          return [];
        },
        groupTabLabel(g) {
          return this.groupHasChanges(g.name) ? g.label + ' *' : g.label;
        },
        findGroup(groupName) {
          return this.groups.find(function (x) { return x.name === groupName; });
        },
        collectChangedItemsForGroup(groupName) {
          const items = [];
          const g = this.findGroup(groupName);
          if (!g) return items;
          (g.sections || []).forEach(function (s) {
            (s.items || []).forEach(function (item) {
              const current = this.formValues[item.id];
              const original = this.originalValues[item.id];
              if (JSON.stringify(current) !== JSON.stringify(original)) {
                items.push({ id: item.id, value: current });
              }
            }, this);
          }, this);
          return items;
        },
        groupHasChanges(groupName) {
          return this.collectChangedItemsForGroup(groupName).length > 0;
        },
        hasAnyChanges() {
          const self = this;
          return this.groups.some(function (g) {
            return self.groupHasChanges(g.name);
          });
        },
        syncOriginalValuesForGroup(groupName) {
          const g = this.findGroup(groupName);
          if (!g) return;
          (g.sections || []).forEach(function (s) {
            (s.items || []).forEach(function (item) {
              this.originalValues[item.id] = this.formValues[item.id];
            }, this);
          }, this);
        },
        beforeGroupLeave(newName, oldName) {
          if (!oldName || !this.groupHasChanges(oldName)) {
            return true;
          }
          return ElementPlus.ElMessageBox.confirm(
            '当前分组有未保存的修改，确定要离开吗？',
            '提示',
            {
              confirmButtonText: '离开',
              cancelButtonText: '取消',
              type: 'warning',
            }
          )
            .then(function () { return true; })
            .catch(function () { return false; });
        },
        initFormValues(groups) {
          const values = {};
          (groups || []).forEach(function (g) {
            (g.sections || []).forEach(function (s) {
              (s.items || []).forEach(function (item) {
                values[item.id] = this.coerceValue(item);
              }, this);
            }, this);
          }, this);
          this.formValues = values;
          this.originalValues = JSON.parse(JSON.stringify(values));
        },
        coerceValue(item) {
          const raw = item.value !== undefined && item.value !== null && item.value !== ''
            ? item.value
            : (item.default || '');
          if (item.type === 'switch') {
            return raw === true || raw === 1 || raw === '1' ? 1 : 0;
          }
          if (item.type === 'number') {
            const n = Number(raw);
            return Number.isFinite(n) ? n : 0;
          }
          if (item.type === 'json' && typeof raw === 'object' && raw !== null) {
            try {
              return JSON.stringify(raw, null, 2);
            } catch (e) {
              return '';
            }
          }
          return raw;
        },
        onGroupChange(name) {
          const g = this.groups.find(function (x) { return x.name === name; });
          if (g && g.sections && g.sections.length && !this.activeSection[name]) {
            this.activeSection[name] = g.sections[0].name;
          }
        },
        async reload() {
          if (this.hasAnyChanges()) {
            try {
              await ElementPlus.ElMessageBox.confirm(
                '有未保存的修改，刷新将丢失修改，确定吗？',
                '提示',
                {
                  confirmButtonText: '刷新',
                  cancelButtonText: '取消',
                  type: 'warning',
                }
              );
            } catch (e) {
              return;
            }
          }
          this.loading = true;
          try {
            const res = await axios.get(this.loadUrl);
            const data = res.data && res.data.data ? res.data.data : {};
            this.groups = data.groups || [];
            if (this.groups.length) {
              this.activeGroup = this.groups[0].name;
              const sectionMap = {};
              this.groups.forEach(function (g) {
                if (g.sections && g.sections.length) {
                  sectionMap[g.name] = g.sections[0].name;
                }
              });
              this.activeSection = sectionMap;
            }
            this.initFormValues(this.groups);
          } catch (e) {
            ElementPlus.ElMessage.error(this.extractMsg(e, '加载配置失败'));
          } finally {
            this.loading = false;
          }
        },
        async saveGroup(g) {
          if (!this.canEdit || !g) return;
          const items = this.collectChangedItemsForGroup(g.name);
          if (!items.length) {
            ElementPlus.ElMessage.warning('当前分组没有修改');
            return;
          }
          this.savingGroup = g.name;
          try {
            await axios.put(this.saveUrl, { items: items });
            ElementPlus.ElMessage.success('保存成功');
            this.syncOriginalValuesForGroup(g.name);
          } catch (e) {
            ElementPlus.ElMessage.error(this.extractMsg(e, '保存失败'));
          } finally {
            this.savingGroup = '';
          }
        },
        extractMsg(e, fallback) {
          const msg = e.response && e.response.data && e.response.data.message;
          if (msg) return msg;
          if (e.response && e.response.data && e.response.data.errors) {
            const errs = e.response.data.errors;
            const first = Object.values(errs)[0];
            if (Array.isArray(first) && first[0]) return first[0];
          }
          return fallback;
        },
      },
    };

    const app = createApp(App);
    app.use(ElementPlus, { locale: ElementPlusLocaleZhCn });
    for (const [key, comp] of Object.entries(ElementPlusIconsVue)) {
      app.component(key, comp);
    }
    app.mount(selector);
  };
})(window);
