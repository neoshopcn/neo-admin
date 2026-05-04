/**
 * 上传控件：POST /admin/api/upload；依赖 Vue、ElementPlus、axios（neoAxiosSetup）
 */
(function (global) {
  function ensureNeoUploadPickerStyles() {
    if (document.getElementById('neo-upload-picker-styles')) {
      return;
    }
    const el = document.createElement('style');
    el.id = 'neo-upload-picker-styles';
    el.textContent = `
      .neo-resource-picker-tooltip.el-popper {
        max-width: 320px !important;
      }
      .neo-resource-picker-tooltip .neo-tip-row {
        font-size: 12px;
        line-height: 1.65;
        color: #e5eaf3;
        word-break: break-all;
      }
      .neo-resource-picker-tooltip .neo-tip-row strong {
        color: #a8abb2;
        font-weight: 600;
        margin-right: 6px;
      }
      .neo-picker-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(128px, 1fr));
        gap: 12px;
        min-height: 120px;
      }
      .neo-picker-card {
        cursor: pointer;
        border: 1px solid #e4e7ed;
        border-radius: 10px;
        overflow: hidden;
        background: #fff;
        transition: box-shadow 0.2s ease, border-color 0.2s ease;
      }
      .neo-picker-card:hover {
        box-shadow: 0 4px 14px rgba(31, 45, 61, 0.12);
        border-color: var(--el-color-primary-light-5);
      }
      .neo-picker-preview {
        height: 108px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(180deg, #f8fafc 0%, #f0f2f5 100%);
        border-bottom: 1px solid #eef1f6;
      }
      .neo-picker-preview img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        display: block;
      }
      .neo-picker-type-box {
        text-align: center;
        padding: 8px 10px;
      }
      .neo-picker-type-ext {
        font-size: 20px;
        font-weight: 700;
        color: var(--el-color-primary);
        letter-spacing: 0.04em;
        line-height: 1.2;
      }
      .neo-picker-type-hint {
        font-size: 11px;
        color: #909399;
        margin-top: 6px;
        line-height: 1.3;
      }
      .neo-picker-title {
        padding: 8px 10px 10px;
        font-size: 12px;
        color: #303133;
        line-height: 1.4;
        word-break: break-all;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: calc(1.4em * 2 + 16px);
      }
    `;
    document.head.appendChild(el);
  }

  const IconsVue = global.ElementPlusIconsVue || {};
  const NeoUploadDeleteIcon =
    IconsVue.Delete ||
    {
      name: 'NeoUploadDeleteFallback',
      template:
        '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z"/></svg>',
    };

  global.NeoUploadField = {
    name: 'NeoUploadField',
    components: {
      Delete: NeoUploadDeleteIcon,
    },
    props: {
      modelValue: { type: String, default: '' },
      action: { type: String, required: true },
      scene: { type: String, default: 'avatar' },
      accept: { type: String, default: 'image/jpeg,image/png,image/gif,image/webp' },
      tip: { type: String, default: '' },
      storageBase: { type: String, default: '' },
      pickFromLibrary: { type: Boolean, default: true },
      resourceListUrl: { type: String, default: '/admin/api/resources' },
      tags: { type: String, default: '' },
      /** 资源库弹窗每页条数 */
      pickerPageSize: { type: Number, default: 12 },
    },
    emits: ['update:modelValue'],
    data() {
      return {
        uploading: false,
        pickerVisible: false,
        pickerLoading: false,
        pickerRows: [],
        pickerPage: 1,
        pickerTotal: 0,
        pickerKeyword: '',
      };
    },
    computed: {
      previewSrc() {
        const v = (this.modelValue || '').trim();
        if (!v) return '';
        if (v.startsWith('http://') || v.startsWith('https://')) return v;
        if (v.startsWith('/')) return v;
        const base = (this.storageBase || '').replace(/\/$/, '');
        if (base) return `${base}/${v.replace(/^\/+/, '')}`;
        return `/storage/${v.replace(/^\/+/, '')}`;
      },
      isImagePreview() {
        const v = (this.modelValue || '').trim();
        if (!v) return false;
        if (/^https?:\/\//i.test(v)) return /\.(jpe?g|png|gif|webp)(\?.*)?$/i.test(v.split('?')[0]);
        return /\.(jpe?g|png|gif|webp)$/i.test(v);
      },
    },
    methods: {
      buildFormData(file) {
        const fd = new FormData();
        fd.append('file', file);
        fd.append('scene', this.scene);
        const t = (this.tags || '').trim();
        if (t) fd.append('tags', t);
        return fd;
      },
      openPicker() {
        ensureNeoUploadPickerStyles();
        this.pickerVisible = true;
        this.pickerKeyword = '';
        this.loadPicker(true);
      },
      async loadPicker(resetPage) {
        if (resetPage) this.pickerPage = 1;
        this.pickerLoading = true;
        try {
          const { data } = await axios.get(this.resourceListUrl, {
            params: {
              page: this.pickerPage,
              page_size: this.pickerPageSize,
              keyword: this.pickerKeyword,
              scene: this.scene,
              status: 1,
            },
          });
          if (data.code !== 0) throw new Error(data.message || '加载失败');
          this.pickerRows = data.data.list || [];
          this.pickerTotal = (data.data.pagination && data.data.pagination.total) || 0;
        } catch (e) {
          ElementPlus.ElMessage.error(
            (e.response && e.response.data && e.response.data.message) || e.message || '加载失败'
          );
        } finally {
          this.pickerLoading = false;
        }
      },
      onPickerPageChange(p) {
        this.pickerPage = p;
        this.loadPicker(false);
      },
      confirmPick(row) {
        if (!row || row.storage_path == null) return;
        this.$emit('update:modelValue', String(row.storage_path));
        this.pickerVisible = false;
        ElementPlus.ElMessage.success('已选择资源');
      },
      async httpRequest(opt) {
        const fd = this.buildFormData(opt.file);
        this.uploading = true;
        try {
          const { data } = await axios.post(this.action, fd);
          if (data.code !== 0) throw new Error(data.message || '上传失败');
          const path = data.data && data.data.path != null ? String(data.data.path) : '';
          this.$emit('update:modelValue', path);
          ElementPlus.ElMessage.success('上传成功');
          opt.onSuccess(data);
        } catch (e) {
          const msg =
            e.response && e.response.data && e.response.data.message
              ? e.response.data.message
              : e.message || '上传失败';
          ElementPlus.ElMessage.error(msg);
          opt.onError(e);
        } finally {
          this.uploading = false;
        }
      },
      async pickerHttpRequest(opt) {
        const fd = this.buildFormData(opt.file);
        this.uploading = true;
        try {
          const { data } = await axios.post(this.action, fd);
          if (data.code !== 0) throw new Error(data.message || '上传失败');
          const path = data.data && data.data.path != null ? String(data.data.path) : '';
          this.$emit('update:modelValue', path);
          ElementPlus.ElMessage.success('上传成功');
          await this.loadPicker(true);
          opt.onSuccess(data);
        } catch (e) {
          const msg =
            e.response && e.response.data && e.response.data.message
              ? e.response.data.message
              : e.message || '上传失败';
          ElementPlus.ElMessage.error(msg);
          opt.onError(e);
        } finally {
          this.uploading = false;
        }
      },
      clear() {
        this.$emit('update:modelValue', '');
      },
      rowPublicUrl(row) {
        if (!row) return '';
        if (row.public_url) return String(row.public_url);
        const p = String(row.storage_path || '').replace(/^\/+/, '');
        if (!p) return '';
        const base = (this.storageBase || '').replace(/\/$/, '');
        if (base) return `${base}/${p}`;
        return `/storage/${p}`;
      },
      rowIsImage(row) {
        if (!row) return false;
        const ext = String(row.extension || '')
          .toLowerCase()
          .replace(/^\./, '');
        if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'avif'].includes(ext)) {
          return true;
        }
        const mime = String(row.mime_type || '').toLowerCase();
        return mime.startsWith('image/');
      },
      rowTypePrimary(row) {
        const ext = String(row.extension || '')
          .replace(/^\./, '')
          .toUpperCase();
        if (ext) return ext.length > 10 ? ext.slice(0, 10) + '…' : ext;
        const mime = String(row.mime_type || '');
        if (mime.includes('/')) return mime.split('/').pop().slice(0, 12).toUpperCase() || 'FILE';
        return 'FILE';
      },
      rowTypeSecondary(row) {
        const mime = String(row.mime_type || '');
        if (mime) return mime.length > 42 ? mime.slice(0, 40) + '…' : mime;
        return '非图片文件';
      },
    },
    template: `
      <div class="neo-upload-field" style="width:100%;">
        <div style="display:flex;gap:12px;align-items:flex-start;flex-wrap:wrap;">
          <div style="flex-shrink:0;display:flex;flex-direction:column;gap:8px;align-items:flex-start;">
            <img v-if="previewSrc && isImagePreview" :src="previewSrc" alt=""
              style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:1px solid #ebeef5;display:block;" />
            <div v-else-if="previewSrc"
              style="max-width:260px;font-size:12px;color:#606266;line-height:1.5;word-break:break-all;border:1px dashed #dcdfe6;padding:8px;border-radius:8px;background:#fafafa;">
              {{ previewSrc }}
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
              <el-button v-if="pickFromLibrary" type="primary" plain size="small" @click="openPicker">
                从资源库选择
              </el-button>
              <el-upload
                v-if="!pickFromLibrary"
                :show-file-list="false"
                :http-request="httpRequest"
                :accept="accept"
                :disabled="uploading"
              >
                <el-button type="primary" plain size="small" :loading="uploading">
                  {{ previewSrc ? '更换文件' : '上传文件' }}
                </el-button>
              </el-upload>
            </div>
          </div>
          <div style="flex:1;min-width:200px;">
            <div v-if="tip" style="font-size:12px;color:#909399;line-height:1.5;">{{ tip }}</div>
            <el-button v-if="modelValue" link type="danger" size="small" circle @click="clear"
              title="清除已选" aria-label="清除已选" style="margin-top:6px;">
              <el-icon :size="16"><Delete /></el-icon>
            </el-button>
          </div>
        </div>

        <el-dialog v-model="pickerVisible" title="从资源库选择" width="820px" destroy-on-close append-to-body>
          <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;align-items:center;">
            <el-input v-model="pickerKeyword" clearable placeholder="搜索文件名 / 路径" style="width:220px"
              @keyup.enter="loadPicker(true)" />
            <el-button @click="loadPicker(true)">查询</el-button>
            <el-upload :show-file-list="false" :http-request="pickerHttpRequest" :accept="accept" :disabled="uploading">
              <el-button type="success" plain :loading="uploading">上传</el-button>
            </el-upload>
            <span style="font-size:12px;color:#909399;margin-left:auto;">当前场景：{{ scene }}</span>
          </div>
          <div v-loading="pickerLoading" style="min-height:140px;">
            <div v-if="!pickerLoading && pickerRows.length===0" style="text-align:center;color:#909399;padding:48px 12px;font-size:13px;">
              暂无资源，可先上传文件
            </div>
            <div v-else-if="pickerRows.length > 0" class="neo-picker-grid">
              <el-tooltip
                v-for="row in pickerRows"
                :key="row.id"
                placement="top"
                effect="dark"
                :show-after="280"
                popper-class="neo-resource-picker-tooltip">
                <template #content>
                  <div class="neo-tip-row"><strong>大小</strong>{{ row.size_label || '—' }}</div>
                  <div class="neo-tip-row"><strong>上传时间</strong>{{ row.uploaded_at || '—' }}</div>
                  <div class="neo-tip-row"><strong>上传人</strong>{{ row.uploader_name || '—' }}</div>
                  <div class="neo-tip-row"><strong>文件路径</strong>{{ row.storage_path || '—' }}</div>
                </template>
                <div class="neo-picker-card" @click="confirmPick(row)">
                  <div class="neo-picker-preview">
                    <img v-if="rowIsImage(row)" :src="rowPublicUrl(row)" :alt="row.original_name || ''" loading="lazy" />
                    <div v-else class="neo-picker-type-box">
                      <div class="neo-picker-type-ext">{{ rowTypePrimary(row) }}</div>
                      <div class="neo-picker-type-hint">{{ rowTypeSecondary(row) }}</div>
                    </div>
                  </div>
                  <div class="neo-picker-title">{{ row.original_name || row.storage_path || '未命名' }}</div>
                </div>
              </el-tooltip>
            </div>
          </div>
          <div style="display:flex;justify-content:flex-end;margin-top:14px;">
            <el-pagination background layout="total, prev, pager, next"
              :total="pickerTotal"
              :page-size="pickerPageSize"
              :current-page="pickerPage"
              @current-change="onPickerPageChange" />
          </div>
          <p style="margin:12px 0 0;font-size:12px;color:#909399;">预览卡片点击即可选用；鼠标悬停卡片可查看大小、时间、上传人与路径。上传成功后会出现在列表中。</p>
        </el-dialog>
      </div>
    `,
  };
})(window);
