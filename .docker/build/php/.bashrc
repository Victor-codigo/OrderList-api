
# ------------------- BASH CUSTOM --------------------- #
parse_git_branch() {
    git branch 2> /dev/null | sed -e '/^[^*]/d' -e 's/* \(.*\)/git: \1 /'
}
export PS1='${debian_chroot:+($debian_chroot)}\[\033[01;32m\]\u@\h\[\033[00;37m\]:\[\033[01;34m\]\w \[\033[01;37m\]| \[\033[01;33m\]$(parse_git_branch)\[\033[00m\]\$ '
export XDEBUG_SESSION=VSCODE

alias sf="bin/console"

alias pu="bin/phpunit --configuration phpunit.xml.dist"
alias puc="bin/phpunit --coverage-clover coverage.xml --configuration phpunit.xml.dist"
alias puchtml="bin/phpunit --configuration phpunit.xml.dist --coverage-html public/code-coverage"
alias puf="bin/phpunit --configuration phpunit.xml.dist --filter"
alias pufc="bin/phpunit --coverage-clover coverage.xml --configuration phpunit.xml.dist --filter"

alias ll="ls -la"

# ----------------------------------------------------- #
