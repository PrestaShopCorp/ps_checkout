<!--**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *-->
<template>
  <div>
    <b-container>
      <b-card class="mt-10">
        <template v-slot:header>
          <i class="material-icons">feedback</i>
          Information
        </template>
        <b-card-body>
          <ul>
            <li>
              PrestaShop version:
              <b>{{ psVersion }}</b>
            </li>
            <li>
              PHP version:
              <b>{{ phpVersion }}</b>
            </li>
            <li>
              Module version:
              <b>{{ moduleVersion }}</b>
            </li>
            <li>
              Shop ID:
              <b>{{ shopId }}</b>
            </li>
            <li>
              Rounding config:
              <b>{{ roundingSettingsIsCorrect }}</b>
            </li>
          </ul>
        </b-card-body>
      </b-card>
    </b-container>

    <b-container class="mt-4">
      <b-card class="mt-10">
        <template v-slot:header>
          <i class="material-icons">settings</i>
          Logger settings
        </template>
        <b-card-body>
          <b-form>
            <b-row>
              <b-col>
                <b-form-group label="Log level" label-for="logger-level">
                  <b-form-select id="logger-level" v-model="loggerLevel">
                    <option
                      v-for="(name, value) in loggerLevels"
                      :key="value"
                      :value="value"
                    >
                      {{ name }}
                    </option>
                  </b-form-select>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group
                  label="Max number of files to keep"
                  label-for="logger-max-files"
                >
                  <b-form-input
                    id="logger-max-files"
                    type="number"
                    min="0"
                    max="30"
                    v-model="loggerMaxFiles"
                  />
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group label="Log HTTP" label-for="logger-http">
                  <b-form-select id="logger-http" v-model="loggerHttp">
                    <option value="0">
                      Disabled
                    </option>
                    <option value="1">
                      Enabled
                    </option>
                  </b-form-select>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group
                  label="Log HTTP Format"
                  label-for="logger-http-format"
                >
                  <b-form-select
                    id="logger-http-format"
                    v-model="loggerHttpFormat"
                  >
                    <option
                      v-for="(name, value) in loggerHttpFormats"
                      :key="value"
                      :value="value"
                    >
                      {{ name }}
                    </option>
                  </b-form-select>
                </b-form-group>
              </b-col>
            </b-row>
          </b-form>
        </b-card-body>
      </b-card>
    </b-container>

    <b-container class="mt-4" v-if="logFileNameSelected">
      <b-card class="mt-10">
        <template v-slot:header>
          <i class="material-icons">receipt</i>
          Log viewer
        </template>
        <b-card-body>
          <b-form>
            <b-row>
              <b-col>
                <b-form-group label="File" label-for="log-files">
                  <b-form-select
                    id="log-files"
                    v-model="logFileNameSelected"
                    @change="onChangeLogFileName(logFileNameSelected)"
                  >
                    <option
                      v-for="(name, value) in logFileNames"
                      :key="value"
                      :value="value"
                    >
                      {{ name }}
                    </option>
                  </b-form-select>
                </b-form-group>
              </b-col>
              <b-col>
                <b-form-group
                  label="Number of lines to load per request (For largest log files)"
                  label-for="log-file-lines"
                >
                  <b-form-select
                    id="log-file-lines"
                    v-model="logFileReaderLimit"
                    @change="onChangeLogFileReaderLimit(logFileReaderLimit)"
                  >
                    <option>
                      250
                    </option>
                    <option selected>
                      500
                    </option>
                    <option>
                      750
                    </option>
                    <option>
                      1000
                    </option>
                  </b-form-select>
                </b-form-group>
              </b-col>
            </b-row>
          </b-form>
          <b-row v-if="logs">
            <b-col>
              <LogViewer :log="logs" />
            </b-col>
          </b-row>
        </b-card-body>
      </b-card>
    </b-container>
  </div>
</template>

<script>
  import LogViewer from '@femessage/log-viewer';
  import ajax from '@/requests/ajax.js';

  export default {
    components: {
      LogViewer
    },
    name: 'Debug',
    computed: {
      moduleVersion() {
        return this.$store.state.context.moduleVersion;
      },
      phpVersion() {
        return this.$store.state.context.phpVersion;
      },
      psVersion() {
        return this.$store.state.context.psVersion;
      },
      shopId() {
        return this.$store.state.context.shopId;
      },
      roundingSettingsIsCorrect() {
        return this.$store.getters.roundingSettingsIsCorrect;
      },
      loggerHttpFormats() {
        return this.$store.state.configuration.logger.httpFormats;
      },
      loggerLevels() {
        return this.$store.state.configuration.logger.levels;
      },
      loggerLevel: {
        get() {
          return this.$store.state.configuration.logger.level;
        },
        set(payload) {
          this.$store.dispatch('changeLoggerLevel', payload);
        }
      },
      loggerMaxFiles: {
        get() {
          return this.$store.state.configuration.logger.maxFiles;
        },
        set(payload) {
          this.$store.dispatch('changeLoggerMaxFiles', payload);
        }
      },
      loggerHttp: {
        get() {
          return this.$store.state.configuration.logger.http;
        },
        set(payload) {
          this.$store.dispatch('changeLoggerHttp', payload);
        }
      },
      loggerHttpFormat: {
        get() {
          return this.$store.state.configuration.logger.httpFormat;
        },
        set(payload) {
          this.$store.dispatch('changeLoggerHttpFormat', payload);
        }
      }
    },
    mounted() {
      this.getLogFileNames();
    },
    data() {
      return {
        logs: '',
        logFileNames: {},
        logFileNameSelected: '',
        logFileReaderOffset: 0,
        logFileReaderLimit: 500
      };
    },
    methods: {
      getLogFileNames() {
        ajax({
          url: this.$store.getters.adminController,
          action: 'GetLogFiles'
        }).then(response => {
          if (typeof response === 'object') {
            this.logFileNames = response;
            if (Object.keys(response)[0] !== undefined) {
              this.logFileNameSelected = Object.keys(response)[0];
              this.getLogs();
            }
          }
        });
      },
      getLogs() {
        let refreshTimer = null;
        ajax({
          url: this.$store.getters.adminController,
          action: 'GetLogs',
          data: {
            file: this.logFileNameSelected,
            offset: this.logFileReaderOffset,
            limit: this.logFileReaderLimit
          }
        }).then(response => {
          if (typeof response === 'object' && response.status === true) {
            this.logFileReaderOffset = response.currentOffset;
            this.logFileReaderLimit = response.limit;
            this.logFileNameSelected = response.file;
            response.lines.forEach(line => {
              this.logs += line;
            });
            refreshTimer = setTimeout(this.getLogs, 10000);
          }
        });
        clearTimeout(refreshTimer);
      },
      onChangeLogFileName(logFileNameSelected) {
        this.logFileNameSelected = logFileNameSelected;
        this.getLogs();
      },
      onChangeLogFileReaderLimit(logFileReaderLimit) {
        this.logFileReaderLimit = logFileReaderLimit;
      }
    }
  };
</script>
