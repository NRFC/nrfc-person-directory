import { execSync } from 'child_process';

async function globalSetup() {
    // Adjust the command prefix for your environment (local wp-cli, wp-env, or docker compose)
    const cmd = 'docker compose exec -T wordpress /var/www/html/wp --allow-root nrfc-person-directory seed-people';

    const output = execSync(cmd, { encoding: 'utf-8' });
    const parsed = JSON.parse(output.trim());

    // Expose seeded post IDs to all Playwright tests via environment variables
    process.env.SEEDED_PERSON_IDS = JSON.stringify(parsed.seeded_ids);
}

export default globalSetup;