
# ------------------- BASH CUSTOM --------------------- #
parse_git_branch() {
    # git branch 2> /dev/null | sed -e '/^[^*]/d' -e 's/* \(.*\)/git: \1 /'
    gitBranch=$(git branch 2> /dev/null | sed -e '/^[^*]/d' -e 's/* \(.*\)/\1 /')

    if [ -z "$gitBranch" ]
    then
        echo "No-Git"
    else
        echo $(parser_git_unicode)" "$gitBranch
    fi
}

parse_folder_unicode() {
    echo -e "\U1F5C0"
}

parser_git_unicode(){
    echo -e "\uE0A0"
}

export PS1='\[\e[38;5;243m\]\d | \A | \[\e[38;5;243;3m\]\s at \[\e[23m\]<\[\e[38;5;154;1m\]\u\[\e[38;5;216m\]@\[\e[38;5;154m\]\H\[\e[0;38;5;243m\]> \[\e[3m\]in \[\e[23m\][\[\e[38;5;45;1m\]$(parse_folder_unicode)  \w\[\e[0;38;5;243m\]] \[\e[3m\]on git \[\e[23m\](\[\e[93;1m\]$(parse_git_branch)\[\e[0;38;5;243m\]) \n\[\e[97m\]\[\e[38;5;154;1m\]\$ \[\e[97m\]❯ \[\e[0m\]'
export XDEBUG_SESSION=VSCODE

alias sf="bin/console"

alias pu="bin/phpunit --configuration phpunit.xml.dist"
alias puc="bin/phpunit --coverage-clover coverage.xml --configuration phpunit.xml.dist"
alias puchtml="bin/phpunit --configuration phpunit.xml.dist --coverage-html public/code-coverage"
alias puf="bin/phpunit --configuration phpunit.xml.dist --filter"
alias pufc="bin/phpunit --coverage-clover coverage.xml --configuration phpunit.xml.dist --filter"
alias puts="phpunit --configuration phpunit.xml.dist --testsuite"

alias ll="ls -la"

# ----------------------------------------------------- #
