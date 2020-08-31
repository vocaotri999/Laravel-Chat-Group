<template>
    <div class="chat-group-main">
        <div class="panel bg-primary text-white">
            <i style="height: 100%" class="far fa-comment-dots"></i> {{ group.name }}
            <a @click.prevent="load()" class="btn-default float-right text-white" style="width: 5%"
               data-toggle="collapse"
               :href="'#collapseExample' + group.id" role="button" aria-expanded="false"
               :aria-controls="'#collapseExample' + group.id">
                <i class="fa fa-angle-down"></i>
            </a>
        </div>
        <div class="collapse" :id="'collapseExample'+ group.id">
            <div class="panel-body chat-panel">
                <ul class="chat">
                    <li v-for="conversation in conversations">
                        <span class="chat-img pull-left">
                            <img src="http://placehold.it/50/55C1E7/fff&text=U" alt="User Avatar" class="img-circle"/>
                        </span>
                        <div class="chat-body clearfix">
                            <div class="header">
                                <strong class="primary-font">{{ conversation.user.name }}</strong>
                            </div>
                            <!--                            v-html="$options.filters.formatMessage(conversation.message)"-->
                            <p>
                                {{ conversation.message }}
                            </p>
                        </div>
                    </li>
                </ul>
            </div>
            <span>&nbsp;</span> <span v-show="typing" class="help-block" style="font-style: italic;">
                            @{{ user.name }} is typing...
                        </span>
            <div class="panel-footer">
                <div class="input-group">
                    <input :id="'emojis-show'+ group.id" type="text" class="form-control input-sm"
                           placeholder="Type your message here..."
                           v-model="message" @keydown="isTyping" @keyup.enter="store()" autofocus/>
                    <span class="input-group-btn">
                            <button class="btn btn-warning btn-sm" style="height: 100%" @click.prevent="store()">
                                Send</button>
                        </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['group'],
    data() {
        return {
            conversations: [],
            message: '',
            user: '',
            group_id: this.group.id,
            typing: false
        }
    },
    mounted() {
        this.listenForNewMessage();
        let _this = this;
        this.listenForTypePing(_this);
    },
    filters: {
        formatMessage: function (value) {
            if (!value) return ''
            value = value.toString()
            return emoji.replace_colons(value)
        }
    },
    methods: {
        isTyping() {
            let channel = Echo.private('chat');
            channel.whisper('typing', {
                user: Laravel.user,
                typing: true
            });
        },
        scrollToEnd: function () {
            let container = this.$el.querySelector("#collapseExample" + this.group_id + " > .chat-panel");
            container.scrollTop = container.scrollHeight;
        },
        load() {
            $("#emojis-show" + this.group.id).emojioneArea({
                filtersPosition: "bottom",
                search: false,
                hidePickerOnBlur: false,
                events: {
                    keydown: function () {
                        let channel = Echo.private('chat');
                        channel.whisper('typing', {
                            user: Laravel.user,
                            typing: true
                        });
                    }
                }
            });
            axios.get('/conversations',
                {
                    params: {
                        group_id: this.group.id
                    }
                })
                .then(async (response) => {
                    this.message = '';
                    this.conversations = [];
                    await response.data.forEach(element => this.conversations.push(element));
                    await this.scrollToEnd();
                });
        },
        store() {
            this.message = $("#emojis-show" + this.group.id)[0].emojioneArea.getText();
            axios.post('/conversations', {message: this.message, group_id: this.group.id})
                .then((response) => {
                    this.message = '';
                    this.conversations.push(response.data);
                    this.scrollToEnd();
                });
        },
        listenForNewMessage() {
            Echo.private('groups.' + this.group.id)
                .listen('NewMessage', (e) => {
                    this.conversations.push(e.conversation);
                    this.scrollToEnd();
                });
        },
        listenForTypePing(_this) {
            Echo.private('chat')
                .listenForWhisper('typing', (e) => {
                    this.user = e.user;
                    this.typing = e.typing;
                    // remove is typing indicator after 1.2s
                    setTimeout(function () {
                        _this.typing = false;
                    }, 1200);
                });
        }
    }
}
</script>

<style scoped>
.chat-group-main {
    margin: 5px auto;
}
</style>
