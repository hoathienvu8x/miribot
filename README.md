*Miribot* is a chat bot system developed in Symfony 3 for research and entertainment purpose.
The bot currently utilize customized version of AIML 2.0 and has no use of database storage.

---

# Personal notes

## Reserved words for AIML
userref: User reference keyword

botref: Bot reference keyword

## Rule of \<star> tags
- All \<star> tags start with index 0 and match either wildcard # _ ^ or *
- If an AIML pattern contains \<set> tag, content of the \<set> tag is a value for wildcard replacement and it follows the rule of \<star> tag index (start at 0)