@echo off
git add general-ledger.php > git_deploy_log.txt 2>&1
git commit -m "UI: Reduce spacing in general ledger" >> git_deploy_log.txt 2>&1
git push origin feature/amar-updates >> git_deploy_log.txt 2>&1
git push origin main >> git_deploy_log.txt 2>&1
echo DONE >> git_deploy_log.txt
