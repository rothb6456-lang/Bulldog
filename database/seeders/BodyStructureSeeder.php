<?php

namespace Database\Seeders;

use App\Models\BodyStructure;
use Illuminate\Database\Seeder;

class BodyStructureSeeder extends Seeder
{
    /**
     * Educational content here is written from well-established general
     * anatomy/exercise-science knowledge. reference_notes is intentionally
     * left blank throughout — specific peer-reviewed citations should be
     * added deliberately later, never fabricated to fill the field.
     */
    public function run(): void
    {
        $rows = [
            // Chest
            ['name' => 'Chest', 'type' => 'muscle', 'region' => 'Chest', 'sort_order' => 1,
                'short_description' => 'The pectoralis major and minor — the primary pushing muscles across the front of the chest.',
                'function_notes' => 'Drives horizontal and incline pressing movements; adducts and internally rotates the shoulder.',
                'common_issues' => 'Tightness here is a common contributor to rounded-shoulder posture; strains typically occur during heavy pressing under fatigue.'],
            ['name' => 'Upper Chest', 'type' => 'muscle', 'region' => 'Chest', 'sort_order' => 2,
                'short_description' => 'The clavicular (upper) head of the pectoralis major, emphasized by incline pressing angles.',
                'function_notes' => 'Contributes to shoulder flexion and horizontal adduction, more active as the pressing angle inclines.',
                'common_issues' => 'Often lags in development without dedicated incline work; rarely a standalone injury site.'],

            // Back
            ['name' => 'Back', 'type' => 'muscle_group', 'region' => 'Back', 'sort_order' => 3,
                'short_description' => 'General term for the posterior trunk musculature — primarily the lats, traps, and rhomboids.',
                'function_notes' => 'Drives pulling movements and spinal/scapular stabilization.',
                'common_issues' => 'Non-specific back tightness is common and usually resolves with balanced pulling volume and mobility work.'],
            ['name' => 'Lats', 'type' => 'muscle', 'region' => 'Back', 'sort_order' => 4,
                'short_description' => 'Latissimus dorsi — the broad, wing-shaped muscle spanning the mid-to-lower back to the upper arm.',
                'function_notes' => 'Primary driver of pulling movements (rows, pull-ups); extends and adducts the shoulder.',
                'common_issues' => 'Tightness can restrict overhead shoulder flexion; strains are uncommon but occur with explosive pulling.'],
            ['name' => 'Rhomboids', 'type' => 'muscle', 'region' => 'Back', 'sort_order' => 5,
                'short_description' => 'Small muscles between the shoulder blades and spine.',
                'function_notes' => 'Retract the scapulae, supporting posture and pulling mechanics.',
                'common_issues' => 'Frequently underdeveloped relative to pressing muscles, contributing to rounded-shoulder posture.'],
            ['name' => 'Traps', 'type' => 'muscle', 'region' => 'Back', 'sort_order' => 6,
                'short_description' => 'Trapezius — a large muscle spanning the neck, shoulders, and mid-back.',
                'function_notes' => 'Elevates, retracts, and rotates the scapula; stabilizes the shoulder girdle during pulling and carrying.',
                'common_issues' => 'Upper traps often become overactive/tight with desk posture; lower traps are commonly underdeveloped.'],
            ['name' => 'Lower Back', 'type' => 'muscle_group', 'region' => 'Back', 'sort_order' => 7,
                'short_description' => 'The erector spinae and surrounding musculature along the lumbar spine.',
                'function_notes' => 'Extends and stabilizes the spine during hinging, squatting, and carrying.',
                'common_issues' => 'One of the most common sites of training-related strain, usually from loaded flexion under fatigue.'],
            ['name' => 'Spine', 'type' => 'region', 'region' => 'Back', 'sort_order' => 8,
                'short_description' => 'The vertebral column and its surrounding stabilizing structures.',
                'function_notes' => 'Provides the structural axis for nearly every lift; spinal stability underlies safe force transfer.',
                'common_issues' => "Training focus here is typically about positioning and bracing rather than isolating the spine itself."],

            // Shoulders
            ['name' => 'Shoulders', 'type' => 'muscle_group', 'region' => 'Shoulders', 'sort_order' => 9,
                'short_description' => 'General term for the deltoid complex and surrounding shoulder musculature.',
                'function_notes' => 'Drives overhead and lateral arm movement; a key stabilizer for pressing and pulling.',
                'common_issues' => 'The shoulder is the most mobile and least inherently stable joint in the body, making it a common site for overuse issues.'],
            ['name' => 'Lateral Delts', 'type' => 'muscle', 'region' => 'Shoulders', 'sort_order' => 10,
                'short_description' => 'The side head of the deltoid.',
                'function_notes' => 'Primary driver of arm abduction (raising the arm out to the side).',
                'common_issues' => 'Rarely injured directly; often a focal point for shoulder-width development.'],
            ['name' => 'Rear Delts', 'type' => 'muscle', 'region' => 'Shoulders', 'sort_order' => 11,
                'short_description' => 'The posterior head of the deltoid.',
                'function_notes' => 'Assists shoulder extension and horizontal abduction; supports posture by countering forward shoulder pull.',
                'common_issues' => 'Commonly underdeveloped relative to the front delts, contributing to shoulder imbalance.'],
            ['name' => 'Anterior Delts', 'type' => 'muscle', 'region' => 'Shoulders', 'sort_order' => 12,
                'short_description' => 'The front head of the deltoid.',
                'function_notes' => 'Assists shoulder flexion and horizontal pressing.',
                'common_issues' => 'Often overworked from high pressing volume relative to pulling — a common source of anterior shoulder fatigue.'],
            ['name' => 'Rotator Cuff', 'type' => 'muscle_group', 'region' => 'Shoulders', 'sort_order' => 13,
                'short_description' => 'A group of four muscles (supraspinatus, infraspinatus, teres minor, subscapularis) wrapping around the shoulder joint.',
                'function_notes' => 'Stabilizes the head of the humerus in the shoulder socket and controls rotation of the upper arm.',
                'common_issues' => 'One of the most common sites of shoulder injury (tendinitis, impingement, tears), especially with repetitive overhead loading or sudden heavy rotational stress — a frequent focus of shoulder rehab programming.'],
            ['name' => 'External Rotators', 'type' => 'muscle_group', 'region' => 'Shoulders', 'sort_order' => 14,
                'short_description' => 'The infraspinatus and teres minor — the rotator cuff muscles responsible for external (outward) rotation of the arm.',
                'function_notes' => 'Balances the often-dominant internal rotators; key for shoulder joint health and overhead stability.',
                'common_issues' => 'Frequently underdeveloped relative to internal rotators (chest/lats) — a common target in shoulder-health and rehab programming.'],
            ['name' => 'Serratus Anterior', 'type' => 'muscle', 'region' => 'Shoulders', 'sort_order' => 15,
                'short_description' => 'A muscle along the side of the ribcage, under the shoulder blade.',
                'function_notes' => 'Rotates and stabilizes the scapula against the ribcage, especially during overhead pressing.',
                'common_issues' => "Weakness here (scapular \"winging\") can contribute to shoulder instability."],

            // Arms
            ['name' => 'Biceps', 'type' => 'muscle', 'region' => 'Arms', 'sort_order' => 16,
                'short_description' => 'Biceps brachii — the two-headed muscle on the front of the upper arm.',
                'function_notes' => 'Flexes the elbow and supinates the forearm.',
                'common_issues' => 'Strains can occur with heavy curling or sudden eccentric load (e.g. catching a heavy weight).'],
            ['name' => 'Triceps', 'type' => 'muscle', 'region' => 'Arms', 'sort_order' => 17,
                'short_description' => 'Triceps brachii — the three-headed muscle on the back of the upper arm.',
                'function_notes' => 'Extends the elbow; the primary driver of pressing lockout.',
                'common_issues' => 'Tendinopathy at the elbow attachment can develop with high-volume pressing.'],
            ['name' => 'Brachialis', 'type' => 'muscle', 'region' => 'Arms', 'sort_order' => 18,
                'short_description' => 'A muscle beneath the biceps, deep in the upper arm.',
                'function_notes' => 'A strong elbow flexor, especially active in neutral- and hammer-grip curling.',
                'common_issues' => 'Rarely injured in isolation; often trained to add overall arm thickness.'],
            ['name' => 'Forearms', 'type' => 'muscle_group', 'region' => 'Arms', 'sort_order' => 19,
                'short_description' => 'The muscles of the lower arm controlling wrist and finger movement.',
                'function_notes' => 'Grip strength, wrist flexion/extension, and stabilization during carries and pulling.',
                'common_issues' => "Overuse (e.g. from heavy carries or high pulling volume) can contribute to elbow tendinopathy (\"tennis\"/\"golfer's\" elbow)."],
            ['name' => 'Fingers', 'type' => 'functional_role', 'region' => 'Arms', 'sort_order' => 20,
                'short_description' => 'The flexor and extensor muscles/tendons controlling grip.',
                'function_notes' => 'Directly responsible for grip strength in carries, pulls, and hangs.',
                'common_issues' => 'Finger flexor tendons can be strained with sudden heavy grip demands.'],
            ['name' => 'Arms', 'type' => 'region', 'region' => 'Arms', 'sort_order' => 21,
                'short_description' => 'General term covering the upper-arm and forearm musculature.',
                'function_notes' => 'Supports elbow flexion/extension and grip across most upper-body movements.',
                'common_issues' => 'See individual arm structures (Biceps, Triceps, Forearms) for specific considerations.'],

            // Core
            ['name' => 'Core', 'type' => 'muscle_group', 'region' => 'Core', 'sort_order' => 22,
                'short_description' => 'The abdominal and deep trunk musculature, including the rectus abdominis and transverse abdominis.',
                'function_notes' => 'Stabilizes the spine and transfers force between the upper and lower body during nearly all lifts.',
                'common_issues' => 'Core fatigue is a common contributor to breakdown in lifting form under heavy load.'],
            ['name' => 'Obliques', 'type' => 'muscle', 'region' => 'Core', 'sort_order' => 23,
                'short_description' => 'The internal and external oblique muscles along the sides of the abdomen.',
                'function_notes' => 'Rotate and laterally flex the trunk; key stabilizers against anti-rotation and side-bending forces.',
                'common_issues' => 'Commonly strained by explosive rotational movement without adequate warm-up.'],

            // Hips & Legs
            ['name' => 'Glutes', 'type' => 'muscle_group', 'region' => 'Hips & Legs', 'sort_order' => 24,
                'short_description' => 'The gluteus maximus, medius, and minimus — the primary hip muscles.',
                'function_notes' => 'Drives hip extension (squatting, hinging) and stabilizes the pelvis during single-leg work.',
                'common_issues' => 'Underactivity here is commonly linked to compensatory low-back or hamstring strain.'],
            ['name' => 'Hamstrings', 'type' => 'muscle', 'region' => 'Hips & Legs', 'sort_order' => 25,
                'short_description' => 'The muscle group on the back of the thigh.',
                'function_notes' => 'Extends the hip and flexes the knee; central to hinging movements like deadlifts.',
                'common_issues' => 'One of the most commonly strained muscles in training, especially with explosive movement or inadequate warm-up.'],
            ['name' => 'Quads', 'type' => 'muscle', 'region' => 'Hips & Legs', 'sort_order' => 26,
                'short_description' => 'Quadriceps femoris — the muscle group on the front of the thigh.',
                'function_notes' => 'Extends the knee; a primary driver of squatting and pressing movements with the legs.',
                'common_issues' => 'Generally resilient; overuse soreness is common after high-volume squat/lunge work.'],
            ['name' => 'Hip Flexors', 'type' => 'muscle_group', 'region' => 'Hips & Legs', 'sort_order' => 27,
                'short_description' => 'Muscles including the iliopsoas that lift the knee toward the torso.',
                'function_notes' => 'Flexes the hip; active in carries, running mechanics, and stabilizing the lower body.',
                'common_issues' => 'Commonly tight from prolonged sitting; can be a source of anterior hip discomfort if overworked without balancing glute/hamstring work.'],
            ['name' => 'Calves', 'type' => 'muscle', 'region' => 'Hips & Legs', 'sort_order' => 28,
                'short_description' => 'The gastrocnemius and soleus on the back of the lower leg.',
                'function_notes' => 'Plantarflexes the ankle; supports propulsion in walking, running, and jumping.',
                'common_issues' => 'Prone to tightness and cramping; strains can occur with sudden explosive movement.'],
            ['name' => 'Tibialis Anterior', 'type' => 'muscle', 'region' => 'Hips & Legs', 'sort_order' => 29,
                'short_description' => 'The muscle along the front of the shin.',
                'function_notes' => 'Dorsiflexes the ankle (lifts the foot); often undertrained relative to the calves.',
                'common_issues' => "Can become a source of shin discomfort (\"shin splints\") with sudden increases in running or jumping volume."],
            ['name' => 'Legs', 'type' => 'region', 'region' => 'Hips & Legs', 'sort_order' => 30,
                'short_description' => 'General term covering the hip, thigh, and lower-leg musculature.',
                'function_notes' => 'Drives squatting, hinging, and locomotion-based movement patterns.',
                'common_issues' => 'See individual leg structures (Quads, Hamstrings, Glutes, Calves) for specific considerations.'],

            // Full Body
            ['name' => 'Full Body', 'type' => 'region', 'region' => 'Full Body', 'sort_order' => 31,
                'short_description' => 'Movements that meaningfully load multiple major muscle groups at once rather than isolating one area.',
                'function_notes' => 'Builds general work capacity, coordination, and conditioning across the whole system.',
                'common_issues' => 'Fatigue management matters more here than in isolation work, since many systems are taxed simultaneously.'],
            ['name' => 'Stabilizers', 'type' => 'functional_role', 'region' => 'Full Body', 'sort_order' => 32,
                'short_description' => 'The secondary muscles that hold a joint steady rather than driving the main movement.',
                'function_notes' => 'Maintain joint position and control under load, especially in unilateral or free-weight movements.',
                'common_issues' => 'Stabilizer fatigue often shows up as form breakdown before the prime mover itself is fully fatigued.'],
        ];

        foreach ($rows as $row) {
            BodyStructure::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}
