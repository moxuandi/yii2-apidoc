new Vue({
    el: '#app',
    methods: {
        // 选择模块后触发
        selectModule(index) {
            let self = this;
            self.activeModule = self.apiData[index];
            self.activeAction = {
                actionId: '',
                actionName: '',
                title: '',
                desc: '',
                route: '',
                method: '',
                //mediaType: '',
                headers: [],
                params: [],
                reqBody: [],
                response: []
            };
            self.requestForm = {
                headers: {},
                params: {},
                data: {},
            };
            self.responseBody = null;
        },
        // 选择接口后触发
        selectAction(index, key) {
            let self = this;
            self.requestForm = {
                headers: {},
                params: {},
                data: {},
            };
            self.responseBody = null;
            self.activeModule.controllers.forEach((item, i) => {
                item.actions.forEach((action, k) => {
                    if (i === index && k === key) {
                        self.activeAction = action;
                        self.activeAction.headers.forEach(item => {
                            if (localStorage.getItem('header_' + item.name)) {
                                self.requestForm.headers[item.name] = localStorage.getItem('header_' + item.name);
                            } else {
                                self.requestForm.headers[item.name] = item.default;
                            }
                        });
                        self.activeAction.params.forEach(item => {
                            self.requestForm.params[item.name] = item.default;
                        });
                        self.requestForm.data = self.formatReqBodyToJson(self.activeAction.reqBody);
                        if (self.requestForm.data) {
                            self.requestForm.data2 = JSON.stringify(self.requestForm.data, null, 4);
                            $('#requestDataView').jsonViewer(self.requestForm.data);
                        }
                        self.formatParams(self.activeAction.headers);
                        self.formatParams(self.activeAction.params);
                        self.formatParams(self.activeAction.reqBody);
                        self.formatParams(self.activeAction.response);
                        action.active = true;
                    } else {
                        action.active = false;
                    }
                });
            });
        },
        // 将请求正文结构转换为json
        formatReqBodyToJson(reqBody) {
            let self = this;
            let data = {};
            reqBody.forEach(item => {
                let defaultVal = '';
                switch (item.type) {
                    case 'integer':
                        defaultVal = item.default ? item.default : 0;
                        break;
                    case 'float':
                        defaultVal = item.default ? item.default : 0;
                        break;
                    case 'boolean':
                        defaultVal = item.default ? item.default : true;
                        break;
                    case 'array':
                        defaultVal = item.default ? item.default : [];
                        break;
                    case 'object':
                        defaultVal = self.formatReqBodyToJson(item.children);
                        break;
                    case 'list':
                        defaultVal = [];
                        defaultVal.push(self.formatReqBodyToJson(item.children));
                        break;
                    default:
                        break;
                }
                data[item.name] = defaultVal;
            });
            return data;
        },
        // 格式化参数列表, 转为二维数组
        formatParams(list) {
            let self = this;
            for (let i = 0; i < list.length; i++) {
                let item = list[i];
                if (item.children && item.children.length) {
                    self.formatParamChildren(item.name, item.children, item.type === 'list');
                    list.splice(i + 1, 0, ...item.children);
                }
            }
        },
        // 递归处理参数子列表
        formatParamChildren(name, children, isList) {
            children.forEach(item => {
                item.name = name + (isList ? '[].' : '.') + item.name;
            });
        },
        // 设置Header参数
        setResHeader(index, name, e) {
            this.requestForm.headers[name] = e.target.value;
            localStorage.setItem('header_' + name, e.target.value);
        },
        // 设置GET参数
        setResParam(index, name, e) {
            this.requestForm.params[name] = e.target.value;
        },
        // 设置POST参数
        setResBody(e) {
            try {
                this.requestForm.data = JSON.parse(e.target.value);
                this.requestForm.data2 = JSON.stringify(this.requestForm.data, null, 4);
                $('#requestDataView').jsonViewer(this.requestForm.data);
            } catch (e) {
                this.resBodyJsonError = true;
                console.log('postData:', '语法错误');
            }
        },
        // 发送请求
        sendRequest() {
            let self = this;
            if (!self.activeAction.actionId) {
                return;
            }
            let params = JSON.parse(JSON.stringify(self.requestForm.params));
            let route = self.formatRoute(self.activeAction.route, params);
            if (route.search(/\{(.+?)\}/g) > -1) {
                alert('路由中还有变量未替换: ' + route);
                return;
            }
            axios({
                method: self.activeAction.method,
                url: route,
                baseURL: self.baseUrl,
                headers: self.requestForm.headers,
                params: self.requestForm.params,
                data: self.requestForm.data
            }).then(res => {
                if (res.status === 200) {
                    self.responseBody = res.data;
                    $('#responseBodyView').jsonViewer(self.responseBody);
                } else {
                    console.log('axios.then', res);
                }
            }).catch(err => {
                console.log('axios.catch', err);
            });
        },
        // 处理路由中的变量
        formatRoute(route, params) {
            var matchs = route.match(/\{(.+?)\}/g);
            if (matchs) {
                matchs.forEach(item => {
                    let key = item.slice(1, item.length - 1);
                    if (params.hasOwnProperty(key) && params[key] !== null) {
                        route = route.replace(item, params[key]);
                        delete params[key];
                    }
                });
            }
            return route;
        }
    },
    created() {
        this.baseUrl = baseUrl;
        this.apiData = apiData;
        this.activeModule = this.apiData[0];
    },
    data: {
        message: 'Hello Vue!',
        baseUrl: '',
        apiData: [],
        activeModule: {
            moduleId: '',
            controllers: []
        },
        activeAction: {
            actionId: '',
            actionName: '',
            title: '',
            desc: '',
            route: '',
            method: '',
            //mediaType: '',
            headers: [],
            params: [],
            reqBody: [],
            response: []
        },
        requestForm: {
            headers: {},
            params: {},
            data: {},
            data2: {},
        },
        responseBody: null,
        resBodyJsonError: false
    }
});
